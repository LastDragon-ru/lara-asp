<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Utils;

use Closure;
use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\EnumTypeDefinitionNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\ListTypeNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\ScalarTypeDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Language\AST\UnionTypeDefinitionNode;
use GraphQL\Language\BlockString;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Argument;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\EnumValueDefinition;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\HasFieldsType;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\NullableType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType;
use GraphQL\Type\Definition\WrappingType;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\ArgumentAlreadyDefined;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\NotImplemented;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\TypeDefinitionAlreadyDefined;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\TypeDefinitionUnknown;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\TypeUnexpected;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Directives\DeprecatedDirective;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Nuwave\Lighthouse\Support\Contracts\Directive;

use function array_merge;
use function assert;
use function is_string;
use function json_encode;
use function sprintf;
use function trim;

use const JSON_THROW_ON_ERROR;

// @phpcs:disable Generic.Files.LineLength.TooLong

class AstManipulator {
    public const Placeholder = '_';

    public function __construct(
        private DirectiveLocator $directiveLocator,
        private DocumentAST $document,
        private TypeRegistry $types,
    ) {
        // empty
    }

    // <editor-fold desc="Getters & Setters">
    // =========================================================================
    protected function getDirectiveLocator(): DirectiveLocator {
        return $this->directiveLocator;
    }

    public function getDocument(): DocumentAST {
        return $this->document;
    }

    protected function getTypes(): TypeRegistry {
        return $this->types;
    }
    // </editor-fold>

    // <editor-fold desc="AST Helpers">
    // =========================================================================}
    /**
     * @param Node|Type|InputObjectField|FieldDefinition|Argument|(TypeDefinitionNode&Node)|string $node
     */
    public function isPlaceholder(
        Node|Type|InputObjectField|FieldDefinition|Argument|TypeDefinitionNode|string $node,
    ): bool {
        // Lighthouse uses `_` type as a placeholder for directives like `@orderBy`
        return $this->getTypeName($node) === static::Placeholder;
    }

    /**
     * @param Node|Type|InputObjectField|FieldDefinition|Argument|(TypeDefinitionNode&Node) $node
     */
    public function isNullable(
        Node|Type|InputObjectField|FieldDefinition|Argument|TypeDefinitionNode $node,
    ): bool {
        $type = null;

        if ($node instanceof InputObjectField || $node instanceof FieldDefinition || $node instanceof Argument) {
            $type = $node->getType();
        } elseif ($node instanceof InputValueDefinitionNode || $node instanceof FieldDefinitionNode) {
            $type = $node->type;
        } elseif ($node instanceof TypeNode || $node instanceof Type) {
            $type = $node;
        } else {
            // empty
        }

        return !($type instanceof NonNull)
            && !($type instanceof NonNullTypeNode);
    }

    /**
     * @param Node|Type|InputObjectField|FieldDefinition|Argument|(TypeDefinitionNode&Node) $node
     */
    public function isList(
        Node|Type|InputObjectField|FieldDefinition|Argument|TypeDefinitionNode $node,
    ): bool {
        $type = null;

        if ($node instanceof InputObjectField || $node instanceof FieldDefinition || $node instanceof Argument) {
            $type = $node->getType();
        } elseif ($node instanceof InputValueDefinitionNode || $node instanceof FieldDefinitionNode) {
            $type = $node->type;
        } elseif ($node instanceof TypeNode || $node instanceof Type) {
            $type = $node;
        } else {
            // empty
        }

        if ($type instanceof NonNull) {
            $type = $type->getWrappedType();
        }

        if ($type instanceof NonNullTypeNode) {
            $type = $type->type;
        }

        return $type instanceof ListOfType
            || $type instanceof ListTypeNode;
    }

    /**
     * @param Node|Type|InputObjectField|FieldDefinition|(TypeDefinitionNode&Node) $node
     */
    public function isUnion(
        Node|Type|InputObjectField|FieldDefinition|TypeDefinitionNode $node,
    ): bool {
        $type = null;

        if ($node instanceof WrappingType) {
            $type = $node->getInnermostType();
        } elseif ($node instanceof Node) {
            try {
                $type = $this->getTypeDefinition($node);
            } catch (TypeDefinitionUnknown) {
                // empty
            }
        } else {
            // empty
        }

        return $type instanceof UnionTypeDefinitionNode
            || $type instanceof UnionType;
    }

    public function isDeprecated(
        Node|Argument|EnumValueDefinition|FieldDefinition|InputObjectField $node,
    ): bool {
        $deprecated = false;

        if ($node instanceof Node) {
            $deprecated = $this->getDirective($node, DeprecatedDirective::class) !== null;
        } else {
            $deprecated = $node->deprecationReason !== null;
        }

        return $deprecated;
    }

    public function isTypeDefinitionExists(string $name): bool {
        try {
            return (bool) $this->getTypeDefinition($name);
        } catch (TypeDefinitionUnknown) {
            return false;
        }
    }

    /**
     * @return (TypeDefinitionNode&Node)|Type
     */
    public function getTypeDefinition(
        Node|Type|InputObjectField|FieldDefinition|Argument|string $node,
    ): TypeDefinitionNode|Type {
        $name       = $this->getTypeName($node);
        $types      = $this->getTypes();
        $definition = $this->getDocument()->types[$name] ?? null;

        if (!$definition) {
            $definition = Type::getStandardTypes()[$name] ?? null;
        }

        if (!$definition && $types->has($name)) {
            $definition = $types->get($name);
        }

        if (!$definition) {
            throw new TypeDefinitionUnknown($name);
        }

        return $definition;
    }

    /**
     * @template TDefinition of (TypeDefinitionNode&Node)|(Type&NamedType)
     *
     * @param TDefinition $definition
     *
     * @return TDefinition
     */
    public function addTypeDefinition(TypeDefinitionNode|Type $definition): TypeDefinitionNode|Type {
        $name = $this->getName($definition);

        if ($this->isTypeDefinitionExists($name)) {
            throw new TypeDefinitionAlreadyDefined($name);
        }

        if ($definition instanceof TypeDefinitionNode && $definition instanceof Node) {
            $this->getDocument()->setTypeDefinition($definition);
        } elseif ($definition instanceof Type) {
            $this->getTypes()->register($definition);
        } else {
            // empty
        }

        return $definition;
    }

    public function removeTypeDefinition(string $name): void {
        if (!$this->isTypeDefinitionExists($name)) {
            throw new TypeDefinitionUnknown($name);
        }

        // Remove
        unset($this->getDocument()->types[$name]);
    }

    /**
     * @template T
     *
     * @param Node|(TypeDefinitionNode&Node)|Type|InputObjectField|FieldDefinition|Argument $node
     * @param class-string<T>                                                               $class
     * @param Closure(T): bool|null                                                         $callback
     *
     * @return (T&Directive)|null
     */
    public function getDirective(
        Node|TypeDefinitionNode|Type|InputObjectField|FieldDefinition|Argument $node,
        string $class,
        ?Closure $callback = null,
    ): ?Directive {
        // todo(graphql): Seems there is no way to attach directive to \GraphQL\Type\Definition\Type?
        // todo(graphql): Should we throw an error if $node has multiple directives?
        $directives = $this->getDirectives($node);
        $found      = null;

        foreach ($directives as $directive) {
            // Class?
            if (!($directive instanceof $class)) {
                continue;
            }

            // Callback?
            if ($callback && !$callback($directive)) {
                continue;
            }

            // Ok
            $found = $directive;
            break;
        }

        return $found;
    }

    /**
     * @template T
     *
     * @param Node|(TypeDefinitionNode&Node)|Type|InputObjectField|FieldDefinition|Argument $node
     * @param class-string<T>|null                                                          $class
     * @param Closure(($class is null ? Directive : T&Directive)): bool|null                $callback
     *
     * @return ($class is null ? array<array-key, Directive> : array<array-key, T&Directive>)
     */
    public function getDirectives(
        Node|TypeDefinitionNode|Type|InputObjectField|FieldDefinition|Argument $node,
        ?string $class = null,
        ?Closure $callback = null,
    ): array {
        $directives = [];

        if ($node instanceof NamedType) {
            if ($node->astNode()) {
                $directives = $this->getDirectives($node->astNode(), $class, $callback);
            }

            foreach ($node->extensionASTNodes() as $extensionNode) {
                $directives = array_merge($directives, $this->getDirectives($extensionNode, $class, $callback));
            }
        } elseif ($node instanceof Node) {
            $associated = $this->getDirectiveLocator()->associated($node)->all();

            if ($class !== null || $callback !== null) {
                foreach ($associated as $directive) {
                    // Class?
                    if ($class && !($directive instanceof $class)) {
                        continue;
                    }

                    // Callback?
                    if ($callback && !$callback($directive)) {
                        continue;
                    }

                    // Ok
                    $directives[] = $directive;
                }
            } else {
                $directives = $associated;
            }
        } elseif ($node instanceof InputObjectField || $node instanceof FieldDefinition || $node instanceof Argument) {
            if ($node->astNode) {
                $directives = $this->getDirectives($node->astNode, $class, $callback);
            }
        } else {
            // empty
        }

        return $directives;
    }

    /**
     * @param Node|Type|InputObjectField|FieldDefinition|Argument|(TypeDefinitionNode&Node)|string $node
     */
    public function getTypeName(
        Node|Type|InputObjectField|FieldDefinition|Argument|TypeDefinitionNode|string $node,
    ): string {
        $name = null;

        if (
            $node instanceof Type
            || $node instanceof InputObjectField
            || $node instanceof FieldDefinition
            || $node instanceof Argument
        ) {
            $type = $node instanceof Type ? $node : $node->getType();

            if ($type instanceof WrappingType) {
                $type = $type->getInnermostType();
            }

            if ($type instanceof NamedType) {
                $name = $type->name();
            }
        } elseif ($node instanceof TypeDefinitionNode) {
            $name = $this->getName($node);
        } elseif ($node instanceof Node) {
            $name = ASTHelper::getUnderlyingTypeName($node);
        } else {
            $name = $node;
        }

        assert($name !== null);

        return $name;
    }

    /**
     * @param InputValueDefinitionNode|(TypeDefinitionNode&Node)|FieldDefinitionNode|InputObjectField|FieldDefinition|Argument|Type $node
     */
    public function getName(
        InputValueDefinitionNode|TypeDefinitionNode|FieldDefinitionNode|InputObjectField|FieldDefinition|Argument|Type $node,
    ): string {
        if ($node instanceof TypeDefinitionNode) {
            $node = $node->getName();
        } elseif ($node instanceof InputValueDefinitionNode || $node instanceof FieldDefinitionNode) {
            $node = $node->name;
        } else {
            // empty
        }

        $name = null;

        if ($node instanceof NameNode) {
            $name = $node->value;
        } elseif ($node instanceof InputObjectField || $node instanceof FieldDefinition || $node instanceof Argument) {
            $name = $node->name;
        } else {
            $name = $this->getTypeName($node);
        }

        return $name;
    }

    /**
     * @param Node|(TypeDefinitionNode&Node)|Type|InputObjectField|FieldDefinition|string $node
     */
    public function getTypeFullName(
        Node|TypeDefinitionNode|Type|InputObjectField|FieldDefinition|string $node,
    ): string {
        $name   = $this->getTypeName($node);
        $node   = $this->getTypeDefinition($name);
        $prefix = null;

        if ($node instanceof InputObjectTypeDefinitionNode || $node instanceof InputObjectType) {
            $prefix = 'input';
        } elseif ($node instanceof ObjectTypeDefinitionNode || $node instanceof ObjectType) {
            $prefix = 'type';
        } elseif ($node instanceof InterfaceTypeDefinitionNode || $node instanceof InterfaceType) {
            $prefix = 'interface';
        } elseif ($node instanceof ScalarTypeDefinitionNode || $node instanceof ScalarType) {
            $prefix = 'scalar';
        } elseif ($node instanceof EnumTypeDefinitionNode || $node instanceof EnumType) {
            $prefix = 'enum';
        } elseif ($node instanceof UnionTypeDefinitionNode || $node instanceof UnionType) {
            $prefix = 'union';
        } else {
            // empty
        }

        return trim("{$prefix} {$name}");
    }

    /**
     * @return array<string, InterfaceTypeDefinitionNode|InterfaceType>
     */
    public function getInterfaces(
        ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode|ObjectType|InterfaceType $node,
    ): array {
        $interfaces     = [];
        $nodeInterfaces = $node instanceof Type
            ? $node->getInterfaces()
            : $node->interfaces;

        foreach ($nodeInterfaces as $interface) {
            $name = $this->getTypeName($interface);

            if ($interface instanceof NamedTypeNode) {
                $interface = $this->getTypeDefinition($interface);
            }

            if ($interface instanceof InterfaceTypeDefinitionNode || $interface instanceof InterfaceType) {
                $interfaces = array_merge(
                    $interfaces,
                    [
                        $name => $interface,
                    ],
                    $this->getInterfaces($interface),
                );
            }
        }

        return $interfaces;
    }

    public function getField(
        InterfaceTypeDefinitionNode|ObjectTypeDefinitionNode|HasFieldsType $node,
        string $name,
    ): FieldDefinitionNode|FieldDefinition|null {
        $field = null;

        if ($node instanceof HasFieldsType) {
            $field = $node->hasField($name) ? $node->getField($name) : null;
        } else {
            foreach ($node->fields as $nodeField) {
                if ($this->getName($nodeField) === $name) {
                    $field = $nodeField;
                    break;
                }
            }
        }

        return $field;
    }

    /**
     * @param callable(InputValueDefinitionNode|Argument): bool $closure
     */
    public function findArgument(
        FieldDefinitionNode|FieldDefinition $node,
        callable $closure,
    ): InputValueDefinitionNode|Argument|null {
        $argument = null;
        $args     = $node instanceof FieldDefinitionNode
            ? $node->arguments
            : $node->args;

        foreach ($args as $arg) {
            if ($closure($arg)) {
                $argument = $arg;
                break;
            }
        }

        return $argument;
    }

    public function getArgument(
        FieldDefinitionNode|FieldDefinition $node,
        string $name,
    ): InputValueDefinitionNode|Argument|null {
        return $this->findArgument(
            $node,
            function (InputValueDefinitionNode|Argument $argument) use ($name): bool {
                return $this->getName($argument) === $name;
            },
        );
    }

    public function getDirectiveNode(Directive|DirectiveNode $directive): ?DirectiveNode {
        $node = null;

        if ($directive instanceof BaseDirective) {
            $node = $directive->directiveNode;
        } elseif ($directive instanceof DirectiveNode) {
            $node = $directive;
        } else {
            // empty
        }

        return $node;
    }

    public function addArgument(
        ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode|ObjectType|InterfaceType $definition,
        FieldDefinitionNode|FieldDefinition $field,
        string $name,
        string $type,
        mixed $default = null,
        string $description = null,
    ): InputValueDefinitionNode|Argument {
        // Added?
        if ($this->getArgument($field, $name)) {
            throw new ArgumentAlreadyDefined(sprintf(
                '%s { %s(%s) }',
                $this->getTypeFullName($definition),
                $this->getName($field),
                $name,
            ));
        }

        // Add
        if ($field instanceof FieldDefinitionNode) {
            $argument = ''
                .($description ? BlockString::print($description) : '')
                ."{$name}: {$type}"
                .($default !== null ? ' = '.json_encode($default, JSON_THROW_ON_ERROR) : '');
            $argument = Parser::inputValueDefinition($argument);

            $field->arguments[] = $argument;
        } else {
            $argument = new Argument([
                'name'         => $name,
                'type'         => $this->getType($type, InputType::class),
                'description'  => $description,
                'defaultValue' => $default,
            ]);

            $field->args[] = $argument;
        }

        // Interfaces
        // (to make sure to get a valid schema)
        $interfaces = $this->getInterfaces($definition);
        $fieldName  = $this->getName($field);

        foreach ($interfaces as $interface) {
            // Field?
            $interfaceField = $this->getField($interface, $fieldName);

            if (!$interfaceField) {
                continue;
            }

            // Update
            $this->addArgument(
                $interface,
                $interfaceField,
                $name,
                $type,
                $default,
                $description,
            );
        }

        // Return
        return $argument;
    }

    /**
     * @param class-string<Directive> $directive
     * @param array<string, mixed>    $arguments
     */
    public function addDirective(
        FieldDefinitionNode|InputValueDefinitionNode|Argument $node,
        string $directive,
        array $arguments = [],
    ): DirectiveNode {
        // Not a Node?
        if ($node instanceof Argument) {
            // Unfortunately directives exists only in AST :(
            // https://github.com/webonyx/graphql-php/issues/588
            if ($node->astNode) {
                return $this->addDirective($node->astNode, $directive, $arguments);
            } else {
                throw new NotImplemented($node::class);
            }
        }

        // Add
        $name               = DirectiveLocator::directiveName($directive);
        $definition         = Parser::directive("@{$name}");
        $node->directives[] = $definition;

        foreach ($arguments as $argument => $value) {
            $definition->arguments[] = Parser::argument($argument.': '.json_encode($value, JSON_THROW_ON_ERROR));
        }

        return $definition;
    }

    /**
     * @template T of FieldDefinitionNode|FieldDefinition
     *
     * @param T                                                           $field
     * @param NamedTypeNode|ListTypeNode|NonNullTypeNode|(Type&InputType) $type
     *
     * @return T
     */
    public function setFieldType(
        ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode|ObjectType|InterfaceType $definition,
        FieldDefinitionNode|FieldDefinition $field,
        TypeNode|Type $type,
    ): FieldDefinitionNode|FieldDefinition {
        // Update
        $this->setType($field, $type);

        // Interfaces
        $interfaces = $this->getInterfaces($definition);
        $fieldName  = $this->getName($field);

        foreach ($interfaces as $interface) {
            // Field?
            $interfaceField = $this->getField($interface, $fieldName);

            if (!$interfaceField) {
                continue;
            }

            // Update
            $this->setType($interfaceField, $type);
        }

        // Return
        return $field;
    }

    /**
     * @template T of InputValueDefinitionNode|Argument
     *
     * @param T                                                           $argument
     * @param NamedTypeNode|ListTypeNode|NonNullTypeNode|(Type&InputType) $type
     *
     * @return T
     */
    public function setArgumentType(
        ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode|ObjectType|InterfaceType $definition,
        FieldDefinitionNode|FieldDefinition $field,
        InputValueDefinitionNode|Argument $argument,
        TypeNode|Type $type,
    ): InputValueDefinitionNode|Argument {
        // Update
        $this->setType($argument, $type);

        // Interfaces
        $interfaces   = $this->getInterfaces($definition);
        $fieldName    = $this->getName($field);
        $argumentName = $this->getName($argument);

        foreach ($interfaces as $interface) {
            // Field?
            $interfaceField = $this->getField($interface, $fieldName);

            if (!$interfaceField) {
                continue;
            }

            // Argument?
            $interfaceArgument = $this->getArgument($interfaceField, $argumentName);

            if ($interfaceArgument === null) {
                continue;
            }

            // Update
            $this->setType($interfaceArgument, $type);
        }

        // Return
        return $argument;
    }

    /**
     * @template T
     *
     * @param (TypeNode&Node)|string $name
     * @param class-string<T>        $expected
     *
     * @return Type&T
     */
    private function getType(TypeNode|string $name, string $expected): Type {
        // todo(graphql): Is there a better way to get Type?
        $type = null;
        $node = is_string($name) ? Parser::typeReference($name) : $name;

        if ($node instanceof ListTypeNode) {
            $type = Type::listOf($this->getType($node->type, Type::class));
        } elseif ($node instanceof NonNullTypeNode) {
            $type = Type::nonNull($this->getType($node->type, NullableType::class));
        } else {
            $type = $this->getTypeDefinition($node);

            if ($type instanceof Node) {
                $type = $this->getTypes()->handle($type);
            }
        }

        if (!($type instanceof $expected)) {
            throw new TypeUnexpected($name, $expected);
        }

        return $type;
    }

    /**
     * @param NamedTypeNode|ListTypeNode|NonNullTypeNode|(Type&InputType) $type
     */
    private function setType(
        FieldDefinitionNode|FieldDefinition|InputValueDefinitionNode|Argument $node,
        TypeNode|Type $type,
    ): void {
        // It seems that we can only modify types of AST nodes :(
        if ($node instanceof Node) {
            $node->type = !($type instanceof Node)
                ? Parser::typeReference($type->toString())
                : $type;
        } else {
            throw new NotImplemented($node::class);
        }
    }
    // </editor-fold>
}
