<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Directives;

use Illuminate\Support\Collection;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionEmpty;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionTooManyOperators;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionTooManyProperties;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\HandlerInvalidConditions;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\OperatorUnsupportedBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\Utils\ArgumentFactory;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Nuwave\Lighthouse\Execution\Arguments\ArgumentSet;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Utils;

use function array_keys;
use function count;
use function reset;

abstract class HandlerDirective extends BaseDirective implements Handler {
    public function __construct(
        private ArgumentFactory $factory,
    ) {
        // empty
    }

    protected function getFactory(): ArgumentFactory {
        return $this->factory;
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
            $argument = $this->getFactory()->getArgument($this->definitionNode, $value);

            if ($argument->value instanceof ArgumentSet) {
                $builder = $this->handle($builder, $argument->value);
            } else {
                throw new HandlerInvalidConditions($this);
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
    public function handle(object $builder, ArgumentSet|Argument $conditions, Property $parent = null): object {
        // Prepare
        $parent   ??= new Property();
        $conditions = $conditions instanceof Argument
            ? $conditions->value
            : $conditions;
        $arguments  = $conditions instanceof ArgumentSet
            ? $conditions->arguments
            : [];

        // Empty?
        if (count($arguments) === 0) {
            return $builder;
        }

        // Property or Operator?
        $first      = reset($arguments);
        $isProperty = $first->directives->filter(Utils::instanceofMatcher(Operator::class))->isEmpty();

        if ($isProperty) {
            // Valid?
            if (count($arguments) !== 1) {
                throw new ConditionTooManyProperties(array_keys($arguments));
            }

            // Process
            foreach ($arguments as $name => $argument) {
                $parent  = $parent->getChild($name);
                $builder = $this->call($builder, $parent, $argument);
            }
        } elseif ($conditions instanceof ArgumentSet || $conditions instanceof Argument) {
            $builder = $this->call($builder, $parent, $conditions);
        } else {
            throw new HandlerInvalidConditions($this);
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
    protected function call(object $builder, Property $property, ArgumentSet|Argument $operator): object {
        // Operator & Value
        /** @var Operator|null $op */
        $op       = null;
        $value    = null;
        $filter   = Utils::instanceofMatcher(Operator::class);
        $operator = $operator instanceof Argument
            ? $operator->value
            : $operator;

        if ($operator instanceof ArgumentSet) {
            if (count($operator->arguments) > 1) {
                throw new ConditionTooManyOperators(
                    array_keys($operator->arguments),
                );
            }

            foreach ($operator->arguments as $argument) {
                /** @var Collection<int, Operator> $operators */
                $operators = $argument->directives->filter($filter);

                if (count($operators) === 1) {
                    $op    = $operators->first();
                    $value = $argument;
                } else {
                    throw new ConditionTooManyOperators(
                        $operators
                            ->map(static function (Operator $operator): string {
                                return $operator::getName();
                            })
                            ->all(),
                    );
                }
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
}
