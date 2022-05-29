<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives;

use Exception;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Builder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\Client\SearchConditionEmpty;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\Client\SearchConditionTooManyOperators;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\Client\SearchConditionTooManyProperties;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\OperatorUnsupportedBuilder;
use LastDragon_ru\LaraASP\GraphQL\Utils\ArgumentFactory;
use LastDragon_ru\LaraASP\GraphQL\Utils\Property;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Nuwave\Lighthouse\Execution\Arguments\ArgumentSet;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgBuilderDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgManipulator;
use Nuwave\Lighthouse\Support\Utils;

use function array_keys;
use function count;
use function reset;

class Directive extends BaseDirective implements ArgManipulator, ArgBuilderDirective, Builder {
    public const Name          = 'SearchBy';
    public const ScalarID      = 'ID';
    public const ScalarInt     = 'Int';
    public const ScalarFloat   = 'Float';
    public const ScalarString  = 'String';
    public const ScalarBoolean = 'Boolean';
    public const ScalarEnum    = self::Name.'Enum';
    public const ScalarNull    = self::Name.'Null';
    public const ScalarLogic   = self::Name.'Logic';
    public const ScalarNumber  = self::Name.'Number';

    public function __construct(
        protected Container $container,
        protected ArgumentFactory $factory,
    ) {
        // empty
    }

    public static function definition(): string {
        return /** @lang GraphQL */ <<<'GRAPHQL'
            """
            Convert Input into Search Conditions.
            """
            directive @searchBy on ARGUMENT_DEFINITION
        GRAPHQL;
    }

    public function manipulateArgDefinition(
        DocumentAST &$documentAST,
        InputValueDefinitionNode &$argDefinition,
        FieldDefinitionNode &$parentField,
        ObjectTypeDefinitionNode &$parentType,
    ): void {
        $this->container
            ->make(Manipulator::class, ['document' => $documentAST])
            ->update($this->directiveNode, $argDefinition);
    }

    /**
     * @inheritDoc
     * @return EloquentBuilder<Model>|QueryBuilder
     */
    public function handleBuilder($builder, $value): EloquentBuilder|QueryBuilder {
        if ($value !== null) {
            $argument = $this->factory->getArgument($this->definitionNode, $value);

            if ($argument->value instanceof ArgumentSet) {
                $builder = $this->where($builder, $argument->value);
            } else {
                throw new Exception('fixme'); // fixme(graphql): Throw error if no definition
            }
        }

        return $builder;
    }

    // <editor-fold desc="Builder">
    // =========================================================================
    public function where(object $builder, ArgumentSet|Argument $conditions, Property $parent = null): object {
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
                throw new SearchConditionTooManyProperties(array_keys($arguments));
            }

            // Process
            foreach ($arguments as $name => $argument) {
                $parent  = $parent->getChild($name);
                $builder = $this->call($builder, $parent, $argument);
            }
        } elseif ($conditions instanceof ArgumentSet || $conditions instanceof Argument) {
            $builder = $this->call($builder, $parent, $conditions);
        } else {
            throw new Exception('fixme'); // fixme(graphql): Throw error
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
                throw new SearchConditionTooManyOperators(
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
                    throw new SearchConditionTooManyOperators(
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
            throw new SearchConditionEmpty();
        }

        // Supported?
        if (!$op->isBuilderSupported($builder)) {
            throw new OperatorUnsupportedBuilder($op, $builder);
        }

        // Return
        return $op->call($this, $builder, $property, $value);
    }
    //</editor-fold>
}
