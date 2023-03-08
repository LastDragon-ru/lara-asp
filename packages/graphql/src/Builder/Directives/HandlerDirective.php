<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Directives;

use Closure;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ListTypeNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\FieldArgument;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
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
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InputSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectFieldArgumentSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectSource;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\NotImplemented;
use LastDragon_ru\LaraASP\GraphQL\Utils\ArgumentFactory;
use Nuwave\Lighthouse\Execution\Arguments\ArgumentSet;
use Nuwave\Lighthouse\Pagination\PaginateDirective;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\AllDirective;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Scout\SearchDirective;
use Nuwave\Lighthouse\Support\Utils;
use ReflectionClass;
use ReflectionFunction;
use ReflectionNamedType;

use function array_keys;
use function count;
use function is_a;
use function is_array;

abstract class HandlerDirective extends BaseDirective implements Handler {
    public function __construct(
        private ArgumentFactory $factory,
        private DirectiveLocator $directives,
    ) {
        // empty
    }

    // <editor-fold desc="Getters / Setters">
    // =========================================================================
    public static function getScope(): string {
        return static::class;
    }

    protected function getFactory(): ArgumentFactory {
        return $this->factory;
    }

    protected function getDirectives(): DirectiveLocator {
        return $this->directives;
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
    protected function handleAnyBuilder(object $builder, mixed $value): object {
        if ($value !== null && $this->definitionNode instanceof InputValueDefinitionNode) {
            $argument   = $this->getFactory()->getArgument($this->definitionNode, $value);
            $isList     = $this->definitionNode->type instanceof ListTypeNode;
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
    // </editor-fold>

    // <editor-fold desc="Manipulate">
    // =========================================================================
    public function manipulateArgDefinition(
        DocumentAST &$documentAST,
        InputValueDefinitionNode &$argDefinition,
        FieldDefinitionNode &$parentField,
        ObjectTypeDefinitionNode &$parentType,
    ): void {
        // Converted?
        /** @var Manipulator $manipulator */
        $manipulator = Container::getInstance()->make(Manipulator::class, [
            'document'    => $documentAST,
            'builderInfo' => $this->getBuilderInfo($parentField),
        ]);

        if ($this->isTypeName($manipulator->getNodeTypeName($argDefinition))) {
            return;
        }

        // Argument
        $argInfo             = new ObjectFieldArgumentSource($manipulator, $parentType, $parentField, $argDefinition);
        $argDefinition->type = $this->getArgDefinitionType($manipulator, $documentAST, $argInfo);

        // Interfaces
        $interfaces   = $manipulator->getNodeInterfaces($parentType);
        $fieldName    = $manipulator->getNodeName($parentField);
        $argumentName = $manipulator->getNodeName($argDefinition);

        foreach ($interfaces as $interface) {
            $field    = $manipulator->getNodeField($interface, $fieldName);
            $argument = $field
                ? $manipulator->getNodeAttribute($field, $argumentName)
                : null;

            if ($argument instanceof InputValueDefinitionNode) {
                $argument->type = $argDefinition->type;
            } elseif ($argument instanceof FieldArgument) {
                throw new NotImplemented($argument::class);
            } else {
                // ignore
            }
        }
    }

    /**
     * Should return `true` if `$name` is already converted.
     */
    abstract protected function isTypeName(string $name): bool;

    abstract protected function getArgDefinitionType(
        Manipulator $manipulator,
        DocumentAST $document,
        ObjectFieldArgumentSource $argument,
    ): ListTypeNode|NamedTypeNode|NonNullTypeNode;

    /**
     * @param class-string<Operator> $operator
     */
    protected function getArgumentTypeDefinitionNode(
        Manipulator $manipulator,
        DocumentAST $document,
        ObjectFieldArgumentSource $argument,
        string $operator,
    ): ListTypeNode|NamedTypeNode|NonNullTypeNode|null {
        // Convert
        $type       = null;
        $node       = null;
        $definition = $manipulator->isPlaceholder($argument->getArgument())
            ? $manipulator->getPlaceholderTypeDefinitionNode($argument->getField())
            : $manipulator->getTypeDefinitionNode($argument->getArgument());

        if ($definition instanceof InputObjectTypeDefinitionNode || $definition instanceof InputObjectType) {
            $node = new InputSource($manipulator, $definition);
        } elseif ($definition instanceof ObjectTypeDefinitionNode || $definition instanceof ObjectType) {
            $node = new ObjectSource($manipulator, $definition);
        } else {
            // empty
        }

        if ($node) {
            $operator = $manipulator->getOperator(static::getScope(), $operator);
            $type     = $operator->getFieldType($manipulator, $node);
            $type     = Parser::typeReference($type);
        }

        // Return
        return $type;
    }

    protected function getBuilderInfo(FieldDefinitionNode $field): BuilderInfo {
        // Scout?
        $scout      = false;
        $directives = $this->getDirectives();

        foreach ($field->arguments as $argument) {
            if ($directives->associatedOfType($argument, SearchDirective::class)->isNotEmpty()) {
                $scout = true;
                break;
            }
        }

        if ($scout) {
            $builder = (new ReflectionClass(ScoutBuilder::class))->newInstanceWithoutConstructor();
            $name    = 'Scout';
            $info    = new BuilderInfo($name, $builder);

            return $info;
        }

        // Query?
        $argument  = 'builder';
        $directive = $directives->associatedOfType($field, AllDirective::class)->first()
            ?? $directives->associatedOfType($field, PaginateDirective::class)->first();
        $resolver  = $directive instanceof BaseDirective && $directive->directiveHasArgument($argument)
            ? $directive->getResolverFromArgument($argument)
            : null;

        if ($resolver instanceof Closure) {
            $type = (new ReflectionFunction($resolver))->getReturnType();
            $type = $type instanceof ReflectionNamedType ? $type->getName() : null;

            if ($type && is_a($type, QueryBuilder::class, true)) {
                $builder = (new ReflectionClass($type))->newInstanceWithoutConstructor();
                $name    = 'Query';
                $info    = new BuilderInfo($name, $builder);

                return $info;
            }
        }

        // Eloquent (default)
        $builder = (new ReflectionClass(EloquentBuilder::class))->newInstanceWithoutConstructor();
        $name    = '';
        $info    = new BuilderInfo($name, $builder);

        return $info;
    }
    // </editor-fold>
}
