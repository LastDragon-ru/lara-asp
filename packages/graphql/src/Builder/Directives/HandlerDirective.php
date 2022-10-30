<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Directives;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ListTypeNode;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Collection;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionEmpty;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionTooManyOperators;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionTooManyProperties;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\HandlerInvalidConditions;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\OperatorUnsupportedBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\Utils\ArgumentFactory;
use Nuwave\Lighthouse\Execution\Arguments\ArgumentSet;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Scout\SearchDirective;
use Nuwave\Lighthouse\Support\Utils;
use ReflectionClass;
use function array_keys;
use function count;
use function is_array;

abstract class HandlerDirective extends BaseDirective implements Handler {
    public function __construct(
        private Container $container,
        private ArgumentFactory $factory,
        private DirectiveLocator $directives,
    ) {
        // empty
    }

    protected function getContainer(): Container {
        return $this->container;
    }

    protected function getFactory(): ArgumentFactory {
        return $this->factory;
    }

    protected function getDirectives(): DirectiveLocator {
        return $this->directives;
    }

    /**
     * @template T of object
     *
     * @param T $builder
     *
     * @return T
     */
    protected function handleAnyBuilder(object $builder, mixed $value): object {
        if ($value !== null) {
            $argument   = $this->getFactory()->getArgument($this->definitionNode, $value);
            $isList     = $this->definitionNode instanceof InputValueDefinitionNode
                && $this->definitionNode->type instanceof ListTypeNode;
            $conditions = $isList && is_array($argument->value)
                ? $argument->value
                : [$argument->value];

            foreach ($conditions as $condition) {
                if ($condition instanceof ArgumentSet) {
                    $builder = $this->handle($builder, new Property(), $condition);
                } else {
                    throw new HandlerInvalidConditions($this);
                }
            }
        }

        return $builder;
    }

    /**
     * @template T of object
     *
     * @param T $builder
     *
     * @return T
     */
    public function handle(object $builder, Property $property, ArgumentSet $conditions): object {
        // Empty?
        if (count($conditions->arguments) === 0) {
            return $builder;
        }

        // Valid?
        if (count($conditions->arguments) !== 1) {
            throw new ConditionTooManyProperties(array_keys($conditions->arguments));
        }

        // Call
        return $this->call($builder, $property, $conditions);
    }

    /**
     * @template T of object
     *
     * @param T $builder
     *
     * @return T
     */
    protected function call(object $builder, Property $property, ArgumentSet $operator): object {
        // Arguments?
        if (count($operator->arguments) > 1) {
            throw new ConditionTooManyOperators(
                array_keys($operator->arguments),
            );
        }

        // Operator & Value
        /** @var Operator|null $op */
        $op     = null;
        $value  = null;
        $filter = Utils::instanceofMatcher(Operator::class);

        foreach ($operator->arguments as $name => $argument) {
            /** @var Collection<int, Operator> $operators */
            $operators = $argument->directives->filter($filter);
            $property  = $property->getChild($name);
            $value     = $argument;
            $op        = $operators->first();

            if (count($operators) > 1) {
                throw new ConditionTooManyOperators(
                    $operators
                        ->map(static function (Operator $operator): string {
                            return $operator::getName();
                        })
                        ->all(),
                );
            }
        }

        // Operator?
        if (!$op || !$value) {
            throw new ConditionEmpty();
        }

        // Supported?
        if (!$op->isBuilderSupported($builder)) {
            throw new OperatorUnsupportedBuilder($op, $builder);
        }

        // Return
        return $op->call($this, $builder, $property, $value);
    }

    protected function getBuilderInfo(FieldDefinitionNode $field): BuilderInfo {
        // Right now, we are supporting detection of Scout and Eloquent builders
        // only (as the most used and easy in implementation). In the future
        // would be good to be more smart...

        // Scout?
        $scout      = false;
        $directives = $this->getDirectives();

        foreach ($field->arguments as $argument) {
            if ($directives->associatedOfType($argument, SearchDirective::class)->isNotEmpty()) {
                $scout = true;
                break;
            }
        }

        // Info
        $info = null;

        if ($scout) {
            $builder = (new ReflectionClass(ScoutBuilder::class))->newInstanceWithoutConstructor();
            $name    = 'Scout';
            $info    = new BuilderInfo($name, $builder);
        } else {
            $builder = (new ReflectionClass(EloquentBuilder::class))->newInstanceWithoutConstructor();
            $name    = '';
            $info    = new BuilderInfo($name, $builder);
        }

        // Return
        return $info;
    }
}
