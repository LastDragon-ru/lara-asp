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
use Illuminate\Support\Collection;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\BuilderInfoProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scope;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\BuilderUnknown;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionEmpty;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionTooManyOperators;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionTooManyProperties;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\HandlerInvalidConditions;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InterfaceFieldArgumentSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectFieldArgumentSource;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\NotImplemented;
use LastDragon_ru\LaraASP\GraphQL\Utils\ArgumentFactory;
use LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator;
use Nuwave\Lighthouse\Execution\Arguments\ArgumentSet;
use Nuwave\Lighthouse\Pagination\PaginateDirective;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\AggregateDirective;
use Nuwave\Lighthouse\Schema\Directives\AllDirective;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Directives\BuilderDirective;
use Nuwave\Lighthouse\Schema\Directives\CountDirective;
use Nuwave\Lighthouse\Schema\Directives\FindDirective;
use Nuwave\Lighthouse\Schema\Directives\FirstDirective;
use Nuwave\Lighthouse\Schema\Directives\RelationDirective;
use Nuwave\Lighthouse\Schema\Directives\WithRelationDirective;
use Nuwave\Lighthouse\Scout\SearchDirective;
use Nuwave\Lighthouse\Support\Contracts\Directive;
use ReflectionFunction;
use ReflectionNamedType;

use function array_keys;
use function array_map;
use function class_exists;
use function count;
use function is_a;
use function is_array;
use function reset;

abstract class HandlerDirective extends BaseDirective implements Handler {
    public function __construct(
        private ArgumentFactory $factory,
        private DirectiveLocator $directives,
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
        return $op->call($this, $builder, $property, $value);
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
        // Builder
        $builder = $this->getBuilderInfo($parentField);

        if (!$builder) {
            $manipulator = Container::getInstance()->make(AstManipulator::class, [
                'document' => $documentAST,
            ]);
            $argSource   = $this->getFieldArgumentSource($manipulator, $parentType, $parentField, $argDefinition);

            throw new BuilderUnknown($argSource);
        }

        // Converted?
        /** @var Manipulator $manipulator */
        $manipulator = Container::getInstance()->make(Manipulator::class, [
            'document'    => $documentAST,
            'builderInfo' => $builder,
        ]);

        if ($this->isTypeName($manipulator->getNodeTypeName($argDefinition))) {
            return;
        }

        // Argument
        $argSource           = $this->getFieldArgumentSource($manipulator, $parentType, $parentField, $argDefinition);
        $argDefinition->type = $this->getArgDefinitionType($manipulator, $documentAST, $argSource);

        // Interfaces
        $interfaces   = $manipulator->getNodeInterfaces($parentType);
        $fieldName    = $manipulator->getNodeName($parentField);
        $argumentName = $manipulator->getNodeName($argDefinition);

        foreach ($interfaces as $interface) {
            // Field?
            $field = $manipulator->getNodeField($interface, $fieldName);

            if (!$field) {
                continue;
            }

            // Argument?
            $argument = $manipulator->getNodeArgument($field, $argumentName);

            if ($argument === null) {
                continue;
            }

            // Directive? (no need to update type here)
            if ($manipulator->getNodeDirective($argument, self::class) !== null) {
                continue;
            }

            // Update
            if ($argument instanceof InputValueDefinitionNode) {
                $argument->type = $argDefinition->type;
            } else {
                throw new NotImplemented($argument::class);
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
        ObjectFieldArgumentSource|InterfaceFieldArgumentSource $argument,
    ): ListTypeNode|NamedTypeNode|NonNullTypeNode;

    /**
     * @param class-string<Operator> $operator
     */
    protected function getArgumentTypeDefinitionNode(
        Manipulator $manipulator,
        DocumentAST $document,
        ObjectFieldArgumentSource|InterfaceFieldArgumentSource $argument,
        string $operator,
    ): ListTypeNode|NamedTypeNode|NonNullTypeNode|null {
        $type       = null;
        $definition = $manipulator->isPlaceholder($argument->getArgument())
            ? $manipulator->getPlaceholderTypeDefinitionNode($argument->getField())
            : $argument->getTypeDefinition();

        if ($definition) {
            $operator = $manipulator->getOperator(static::getScope(), $operator);
            $node     = $manipulator->getTypeSource($definition);
            $type     = $operator->getFieldType($manipulator, $node);
            $type     = Parser::typeReference($type);
        }

        return $type;
    }

    protected function getBuilderInfo(FieldDefinitionNode $field): ?BuilderInfo {
        // Provider?
        $directives = $this->getDirectives();
        $provider   = $directives->associated($field)->first(static function (Directive $directive): bool {
            return $directive instanceof BuilderInfoProvider;
        });

        if ($provider instanceof BuilderInfoProvider) {
            return $this->getBuilderInfoInstance($provider);
        }

        // Scout?
        $scout = false;

        foreach ($field->arguments as $argument) {
            if ($directives->associatedOfType($argument, SearchDirective::class)->isNotEmpty()) {
                $scout = true;
                break;
            }
        }

        if ($scout) {
            return $this->getBuilderInfoInstance(ScoutBuilder::class);
        }

        // Builder?
        $directive = $directives->associated($field)->first(static function (Directive $directive): bool {
            return $directive instanceof AllDirective
                || $directive instanceof PaginateDirective
                || $directive instanceof BuilderDirective
                || $directive instanceof RelationDirective
                || $directive instanceof FirstDirective
                || $directive instanceof FindDirective
                || $directive instanceof CountDirective
                || $directive instanceof AggregateDirective
                || $directive instanceof WithRelationDirective;
        });

        if ($directive) {
            $type = null;

            if ($directive instanceof PaginateDirective) {
                $type = $this->getBuilderType($directive, 'builder');
            } elseif ($directive instanceof AllDirective) {
                $type = $this->getBuilderType($directive, 'builder');
            } elseif ($directive instanceof AggregateDirective) {
                $type = $this->getBuilderType($directive, 'builder');
            } elseif ($directive instanceof BuilderDirective) {
                $type = $this->getBuilderType($directive, 'method');
            } else {
                // empty
            }

            return $this->getBuilderInfoInstance($type ?? EloquentBuilder::class);
        }

        // Unknown
        return null;
    }

    /**
     * @return class-string|null
     */
    private function getBuilderType(BaseDirective $directive, string ...$arguments): ?string {
        $type = null;

        foreach ($arguments as $argument) {
            if ($directive->directiveHasArgument($argument)) {
                $resolver = $directive->getResolverFromArgument($argument);
                $return   = (new ReflectionFunction($resolver))->getReturnType();
                $return   = $return instanceof ReflectionNamedType
                    ? $return->getName()
                    : null;

                if ($return && class_exists($return)) {
                    $type = $return;
                }

                break;
            }
        }

        return $type;
    }

    private function getBuilderInfoInstance(BuilderInfoProvider|BuilderInfo|string $type): ?BuilderInfo {
        return match (true) {
            $type instanceof BuilderInfo              => $type,
            $type instanceof BuilderInfoProvider      => $this->getBuilderInfoInstance($type->getBuilderInfo()),
            is_a($type, EloquentBuilder::class, true) => new BuilderInfo('', EloquentBuilder::class),
            is_a($type, ScoutBuilder::class, true)    => new BuilderInfo('Scout', ScoutBuilder::class),
            is_a($type, QueryBuilder::class, true)    => new BuilderInfo('Query', QueryBuilder::class),
            is_a($type, Collection::class, true)      => new BuilderInfo('Collection', Collection::class),
            default                                   => null,
        };
    }

    private function getFieldArgumentSource(
        AstManipulator $manipulator,
        ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode $type,
        FieldDefinitionNode $field,
        InputValueDefinitionNode $argument,
    ): ObjectFieldArgumentSource|InterfaceFieldArgumentSource {
        return $type instanceof InterfaceTypeDefinitionNode
            ? new InterfaceFieldArgumentSource($manipulator, $type, $field, $argument)
            : new ObjectFieldArgumentSource($manipulator, $type, $field, $argument);
    }
    // </editor-fold>
}
