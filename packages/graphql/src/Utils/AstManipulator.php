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
use GraphQL\Language\AST\UnionTypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\FieldArgument;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\TypeWithFields;
use GraphQL\Type\Definition\UnionType;
use GraphQL\Type\Definition\WrappingType;
use Illuminate\Support\Collection;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\TypeDefinitionAlreadyDefined;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\TypeDefinitionUnknown;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Nuwave\Lighthouse\Support\Contracts\Directive;

use function array_merge;
use function trim;

abstract class AstManipulator {
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
    public function isPlaceholder(Node|InputObjectField|FieldArgument|string $node): bool {
        // Lighthouse uses `_` type as a placeholder for directives like `@orderBy`
        return $this->getNodeTypeName($node) === '_';
    }

    public function isNullable(
        InputValueDefinitionNode|FieldDefinitionNode|InputObjectField|FieldDefinition|FieldArgument $node,
    ): bool {
        $isNullable = true;

        if ($node instanceof InputObjectField || $node instanceof FieldDefinition || $node instanceof FieldArgument) {
            $isNullable = !($node->getType() instanceof NonNull);
        } else {
            $isNullable = !($node->type instanceof NonNullTypeNode);
        }

        return $isNullable;
    }

    public function isList(
        InputValueDefinitionNode|FieldDefinitionNode|InputObjectField|FieldDefinition|FieldArgument|TypeDefinitionNode|Type $node,
    ): bool {
        $type = null;

        if ($node instanceof InputObjectField || $node instanceof FieldDefinition || $node instanceof FieldArgument) {
            $type = $node->getType();

            if ($type instanceof NonNull) {
                $type = $type->getWrappedType(false);
            }
        } elseif ($node instanceof InputValueDefinitionNode || $node instanceof FieldDefinitionNode) {
            $type = $node->type;

            if ($type instanceof NonNullTypeNode) {
                $type = $type->type;
            }
        } else {
            // empty
        }

        return $type instanceof ListOfType
            || $type instanceof ListTypeNode;
    }

    public function isUnion(
        InputValueDefinitionNode|FieldDefinitionNode|InputObjectField|FieldDefinition|TypeDefinitionNode|Type $node,
    ): bool {
        $type = null;

        if ($node instanceof WrappingType) {
            $type = $node->getWrappedType(true);
        } elseif ($node instanceof InputValueDefinitionNode || $node instanceof FieldDefinitionNode) {
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
        Node|InputObjectField|FieldDefinition|FieldArgument|string $node,
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
     * @template TInterface of TypeDefinitionNode
     * @template TClass of Node
     *
     * @param TInterface&TClass $definition
     *
     * @return TInterface&TClass
     */
    public function addTypeDefinition(TypeDefinitionNode $definition): TypeDefinitionNode {
        $name = $this->getNodeName($definition);

        if ($this->isTypeDefinitionExists($name)) {
            throw new TypeDefinitionAlreadyDefined($name);
        }

        $this->getDocument()->setTypeDefinition($definition);

        return $definition;
    }

    public function removeTypeDefinition(string $name): void {
        if (!$this->isTypeDefinitionExists($name)) {
            throw new TypeDefinitionUnknown($name);
        }

        // Remove
        unset($this->getDocument()->types[$name]);
    }

    public function getScalarTypeDefinitionNode(string $scalar): ScalarTypeDefinitionNode {
        // It can be defined inside schema
        $node = null;

        try {
            $node = $this->getTypeDefinitionNode($scalar);
        } catch (TypeDefinitionUnknown) {
            // empty
        }

        if (!$node) {
            // or programmatically (and there is no definition...)
            $node = Parser::scalarTypeDefinition("scalar {$scalar}");
        } elseif (!($node instanceof ScalarTypeDefinitionNode)) {
            throw new TypeDefinitionUnknown($scalar);
        } else {
            // empty
        }

        return $node;
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
        Node|TypeDefinitionNode|Type|InputObjectField|FieldDefinition|FieldArgument $node,
        string $class,
        ?Closure $callback = null,
    ): ?Directive {
        // todo(graphql): Seems there is no way to attach directive to \GraphQL\Type\Definition\Type?
        // todo(graphql): Should we throw an error if $node has multiple directives?
        return $this->getNodeDirectives($node, $class, $callback)->first();
    }

    /**
     * @template T
     *
     * @param class-string<T>       $class
     * @param Closure(T): bool|null $callback
     *
     * @return Collection<int, T&Directive>
     */
    public function getNodeDirectives(
        Node|TypeDefinitionNode|Type|InputObjectField|FieldDefinition|FieldArgument $node,
        string $class,
        ?Closure $callback = null,
    ): Collection {
        /** @var Collection<int, T&Directive> $directives */
        $directives = new Collection();

        if ($node instanceof Node) {
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
        }

        return $directives;
    }

    public function getNodeTypeName(
        Node|Type|InputObjectField|FieldDefinition|FieldArgument|TypeDefinitionNode|string $node,
    ): string {
        $name = null;

        if ($node instanceof Type || $node instanceof InputObjectField || $node instanceof FieldDefinition || $node instanceof FieldArgument) {
            $type = $node instanceof Type ? $node : $node->getType();

            if ($type instanceof WrappingType) {
                $name = $type->getWrappedType(true)->name;
            } else {
                $name = $type->name;
            }
        } elseif ($node instanceof TypeDefinitionNode) {
            $name = $this->getNodeName($node);
        } elseif ($node instanceof Node) {
            $name = ASTHelper::getUnderlyingTypeName($node);
        } else {
            $name = $node;
        }

        return $name;
    }

    public function getNodeName(
        InputValueDefinitionNode|TypeDefinitionNode|FieldDefinitionNode|InputObjectField|FieldDefinition|FieldArgument|Type $node,
    ): string {
        // fixme(graphql-php): in v15 the `TypeDefinitionNode::getName()` should be used instead.
        $name = $node->name;

        if ($name instanceof NameNode) {
            $name = $name->value;
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
        InterfaceTypeDefinitionNode|ObjectTypeDefinitionNode|TypeWithFields $node,
        string $name,
    ): FieldDefinitionNode|FieldDefinition|null {
        $field = null;

        if ($node instanceof TypeWithFields) {
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
    ): InputValueDefinitionNode|FieldArgument|null {
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
