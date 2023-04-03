<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Utils;

use Closure;
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
use GraphQL\Type\Definition\Argument;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\HasFieldsType;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType;
use GraphQL\Type\Definition\WrappingType;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\TypeDefinitionAlreadyDefined;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\TypeDefinitionUnknown;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Nuwave\Lighthouse\Support\Contracts\Directive;

use function array_merge;
use function assert;
use function reset;
use function trim;

// @phpcs:disable Generic.Files.LineLength.TooLong

class AstManipulator {
    public function __construct(
        private DirectiveLocator $directives,
        private DocumentAST $document,
        private TypeRegistry $types,
    ) {
        // empty
    }

    // <editor-fold desc="Getters & Setters">
    // =========================================================================
    protected function getDirectives(): DirectiveLocator {
        return $this->directives;
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
    public function isPlaceholder(
        Node|Type|InputObjectField|FieldDefinition|Argument|TypeDefinitionNode|string $node,
    ): bool {
        // Lighthouse uses `_` type as a placeholder for directives like `@orderBy`
        return $this->getNodeTypeName($node) === '_';
    }

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

    public function isUnion(
        Node|Type|InputObjectField|FieldDefinition|TypeDefinitionNode $node,
    ): bool {
        $type = null;

        if ($node instanceof WrappingType) {
            $type = $node->getInnermostType();
        } elseif ($node instanceof Node) {
            try {
                $type = $this->getTypeDefinitionNode($node);
            } catch (TypeDefinitionUnknown) {
                // empty
            }
        } else {
            // empty
        }

        return $type instanceof UnionTypeDefinitionNode
            || $type instanceof UnionType;
    }

    public function isTypeDefinitionExists(string $name): bool {
        try {
            return (bool) $this->getTypeDefinitionNode($name);
        } catch (TypeDefinitionUnknown) {
            return false;
        }
    }

    public function getTypeDefinitionNode(
        Node|Type|InputObjectField|FieldDefinition|Argument|string $node,
    ): TypeDefinitionNode|Type {
        $name       = $this->getNodeTypeName($node);
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
        $name = $this->getNodeName($definition);

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
     * @param class-string<T>       $class
     * @param Closure(T): bool|null $callback
     *
     * @return (T&Directive)|null
     */
    public function getNodeDirective(
        Node|TypeDefinitionNode|Type|InputObjectField|FieldDefinition|Argument $node,
        string $class,
        ?Closure $callback = null,
    ): ?Directive {
        // todo(graphql): Seems there is no way to attach directive to \GraphQL\Type\Definition\Type?
        // todo(graphql): Should we throw an error if $node has multiple directives?
        $directives = $this->getNodeDirectives($node, $class, $callback);
        $directive  = reset($directives) ?: null;

        return $directive;
    }

    /**
     * @template T
     *
     * @param class-string<T>       $class
     * @param Closure(T): bool|null $callback
     *
     * @return list<T&Directive>
     */
    public function getNodeDirectives(
        Node|TypeDefinitionNode|Type|InputObjectField|FieldDefinition|Argument $node,
        string $class,
        ?Closure $callback = null,
    ): array {
        $directives = [];

        if ($node instanceof NamedType) {
            if ($node->astNode()) {
                $directives = $this->getNodeDirectives($node->astNode(), $class, $callback);
            } else {
                foreach ($node->extensionASTNodes() as $extensionNode) {
                    $directives = array_merge($directives, $this->getNodeDirectives($extensionNode, $class, $callback));
                }
            }
        } elseif ($node instanceof Node) {
            $associated = $this->getDirectives()->associated($node);

            foreach ($associated as $directive) {
                // Class?
                if (!($directive instanceof $class)) {
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
            // empty
        }

        return $directives;
    }

    public function getNodeTypeName(
        Node|Type|InputObjectField|FieldDefinition|Argument|TypeDefinitionNode|string $node,
    ): string {
        $name = null;

        if ($node instanceof Type || $node instanceof InputObjectField || $node instanceof FieldDefinition || $node instanceof Argument) {
            $type = $node instanceof Type ? $node : $node->getType();

            if ($type instanceof WrappingType) {
                $type = $type->getInnermostType();
            }

            if ($type instanceof NamedType) {
                $name = $type->name();
            }
        } elseif ($node instanceof TypeDefinitionNode) {
            $name = $this->getNodeName($node);
        } elseif ($node instanceof Node) {
            $name = ASTHelper::getUnderlyingTypeName($node);
        } else {
            $name = $node;
        }

        assert($name !== null);

        return $name;
    }

    public function getNodeName(
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
            $name = $this->getNodeTypeName($node);
        }

        return $name;
    }

    public function getNodeTypeFullName(
        Node|TypeDefinitionNode|Type|InputObjectField|FieldDefinition|string $node,
    ): string {
        $name   = $this->getNodeTypeName($node);
        $node   = $this->getTypeDefinitionNode($name);
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
    public function getNodeInterfaces(
        ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode|ObjectType|InterfaceType $node,
    ): array {
        $interfaces     = [];
        $nodeInterfaces = $node instanceof Type
            ? $node->getInterfaces()
            : $node->interfaces;

        foreach ($nodeInterfaces as $interface) {
            $name = $this->getNodeTypeName($interface);

            if ($interface instanceof NamedTypeNode) {
                $interface = $this->getTypeDefinitionNode($interface);
            }

            if ($interface instanceof InterfaceTypeDefinitionNode || $interface instanceof InterfaceType) {
                $interfaces = array_merge(
                    $interfaces,
                    [
                        $name => $interface,
                    ],
                    $this->getNodeInterfaces($interface),
                );
            }
        }

        return $interfaces;
    }

    public function getNodeField(
        InterfaceTypeDefinitionNode|ObjectTypeDefinitionNode|HasFieldsType $node,
        string $name,
    ): FieldDefinitionNode|FieldDefinition|null {
        $field = null;

        if ($node instanceof HasFieldsType) {
            $field = $node->hasField($name) ? $node->getField($name) : null;
        } else {
            foreach ($node->fields as $nodeField) {
                if ($this->getNodeName($nodeField) === $name) {
                    $field = $nodeField;
                    break;
                }
            }
        }

        return $field;
    }

    public function getNodeAttribute(
        FieldDefinitionNode|FieldDefinition $node,
        string $name,
    ): InputValueDefinitionNode|Argument|null {
        $attribute = null;

        if ($node instanceof FieldDefinition) {
            $attribute = $node->getArg($name);
        } else {
            foreach ($node->arguments as $nodeArgument) {
                if ($this->getNodeName($nodeArgument) === $name) {
                    $attribute = $nodeArgument;
                    break;
                }
            }
        }

        return $attribute;
    }
    //</editor-fold>
}
