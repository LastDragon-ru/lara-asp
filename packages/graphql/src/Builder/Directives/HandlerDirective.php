<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Directives;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\ListTypeNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\Parser;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfoDetector;
use LastDragon_ru\LaraASP\GraphQL\Builder\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Context\HandlerContextBuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Context\HandlerContextImplicit;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context as ContextContract;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scope;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionEmpty;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionTooManyOperators;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionTooManyProperties;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\HandlerInvalidConditions;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InterfaceFieldArgumentSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectFieldArgumentSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\WithManipulator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\WithSource;
use LastDragon_ru\LaraASP\GraphQL\Utils\ArgumentFactory;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Nuwave\Lighthouse\Execution\Arguments\ArgumentSet;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Override;

use function array_map;
use function count;
use function is_array;
use function reset;

/**
 * @see HandlerContextBuilderInfo
 * @see HandlerContextImplicit
 */
abstract class HandlerDirective extends BaseDirective implements Handler {
    use WithManipulator;
    use WithSource;

    public function __construct(
        private ArgumentFactory $factory,
    ) {
        // empty
    }

    // <editor-fold desc="Getters / Setters">
    // =========================================================================
    /**
     * @return class-string<Scope>
     */
    abstract public static function getScope(): string;

    protected function getFactory(): ArgumentFactory {
        return $this->factory;
    }
    // </editor-fold>

    // <editor-fold desc="Handle">
    // =========================================================================
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
     *
     * @param EloquentBuilder<Model>|QueryBuilder $builder
     *
     * @return EloquentBuilder<Model>|QueryBuilder
     */
    public function handleBuilder($builder, mixed $value): EloquentBuilder|QueryBuilder {
        return $this->handleAnyBuilder($builder, $value);
    }

    public function handleScoutBuilder(ScoutBuilder $builder, mixed $value): ScoutBuilder {
        return $this->handleAnyBuilder($builder, $value);
    }

    /**
     * @template T of object
     *
     * @param T $builder
     *
     * @return T
     */
    protected function handleAnyBuilder(object $builder, mixed $value, ContextContract $context = null): object {
        if ($value !== null && $this->definitionNode instanceof InputValueDefinitionNode) {
            $argument   = !($value instanceof Argument)
                ? $this->getFactory()->getArgument($this->definitionNode, $value)
                : $value;
            $isList     = $this->definitionNode->type instanceof ListTypeNode;
            $conditions = $isList && is_array($argument->value)
                ? $argument->value
                : [$argument->value];

            foreach ($conditions as $condition) {
                if ($condition instanceof ArgumentSet) {
                    $builder = $this->handle($builder, new Property(), $condition, $context ?? new Context());
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
    #[Override]
    public function handle(
        object $builder,
        Property $property,
        ArgumentSet $conditions,
        ContextContract $context,
    ): object {
        // Empty?
        if (count($conditions->arguments) === 0) {
            return $builder;
        }

        // Valid?
        if (count($conditions->arguments) !== 1) {
            throw new ConditionTooManyProperties(
                ArgumentFactory::getArgumentsNames($conditions),
            );
        }

        // Call
        return $this->call($builder, $property, $conditions, $context);
    }

    /**
     * @template T of object
     *
     * @param T $builder
     *
     * @return T
     */
    protected function call(
        object $builder,
        Property $property,
        ArgumentSet $operator,
        ContextContract $context,
    ): object {
        // Arguments?
        if (count($operator->arguments) > 1) {
            throw new ConditionTooManyOperators(
                ArgumentFactory::getArgumentsNames($operator),
            );
        }

        // Operator & Value
        $op    = null;
        $value = null;

        foreach ($operator->arguments as $name => $argument) {
            $operators = [];

            foreach ($argument->directives as $directive) {
                if ($directive instanceof Operator) {
                    $operators[] = $directive;
                }
            }

            $property = $property->getChild($name);
            $value    = $argument;
            $op       = reset($operators);

            if (count($operators) > 1) {
                throw new ConditionTooManyOperators(
                    array_map(
                        static function (Operator $operator): string {
                            return $operator::getName();
                        },
                        $operators,
                    ),
                );
            }
        }

        // Operator?
        if (!$op || !$value) {
            throw new ConditionEmpty();
        }

        // Return
        return $op->call($this, $builder, $property, $value, $context);
    }
    // </editor-fold>

    // <editor-fold desc="Manipulate">
    // =========================================================================
    public function manipulateArgDefinition(
        DocumentAST &$documentAST,
        InputValueDefinitionNode &$argDefinition,
        FieldDefinitionNode &$parentField,
        ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode &$parentType,
    ): void {
        // Converted?
        $detector    = Container::getInstance()->make(BuilderInfoDetector::class);
        $builder     = $detector->getFieldArgumentBuilderInfo($documentAST, $parentType, $parentField, $argDefinition);
        $manipulator = $this->getAstManipulator($documentAST);

        if ($this->isTypeName($manipulator->getTypeName($argDefinition))) {
            return;
        }

        // Argument
        $context = (new Context())->override([
            HandlerContextBuilderInfo::class => new HandlerContextBuilderInfo($builder),
            HandlerContextImplicit::class    => new HandlerContextImplicit(
                $manipulator->isPlaceholder($argDefinition),
            ),
        ]);
        $source  = $this->getFieldArgumentSource($manipulator, $parentType, $parentField, $argDefinition);
        $type    = $this->getArgDefinitionType($manipulator, $documentAST, $source, $context);

        $manipulator->setArgumentType(
            $parentType,
            $parentField,
            $argDefinition,
            $type,
        );
    }

    /**
     * Should return `true` if `$name` is already converted.
     */
    abstract protected function isTypeName(string $name): bool;

    abstract protected function getArgDefinitionType(
        Manipulator $manipulator,
        DocumentAST $document,
        ObjectFieldArgumentSource|InterfaceFieldArgumentSource $argument,
        ContextContract $context,
    ): ListTypeNode|NamedTypeNode|NonNullTypeNode;

    /**
     * @param class-string<Operator> $operator
     */
    protected function getArgumentTypeDefinitionNode(
        Manipulator $manipulator,
        DocumentAST $document,
        ObjectFieldArgumentSource|InterfaceFieldArgumentSource $argument,
        ContextContract $context,
        string $operator,
    ): ListTypeNode|NamedTypeNode|NonNullTypeNode|null {
        $definition = $context->get(HandlerContextImplicit::class)?->value
            ? $manipulator->getTypeDefinition($manipulator->getOriginType($argument->getField()))
            : $argument->getTypeDefinition();
        $operator   = $manipulator->getOperator(static::getScope(), $operator);
        $source     = $manipulator->getTypeSource($definition);
        $type       = $operator->getFieldType($manipulator, $source, $context);
        $type       = Parser::typeReference($type);

        return $type;
    }
    // </editor-fold>
}
