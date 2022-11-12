<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Utils;

use Exception;
use GraphQL\Language\AST\EnumTypeDefinitionNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ListTypeNode;
use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\ScalarTypeDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\AST\UnionTypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ListOfType;
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
    public function isPlaceholder(Node|InputObjectField|string $node): bool {
        // Lighthouse uses `_` type as a placeholder for directives like `@orderBy`
        return $this->getNodeTypeName($node) === '_';
    }

    public function isNullable(
        InputValueDefinitionNode|FieldDefinitionNode|InputObjectField|FieldDefinition $node,
    ): bool {
        $isNullable = true;

        if ($node instanceof InputObjectField || $node instanceof FieldDefinition) {
            $isNullable = !($node->getType() instanceof NonNull);
        } else {
            $isNullable = !($node->type instanceof NonNullTypeNode);
        }

        return $isNullable;
    }

    public function isList(
        InputValueDefinitionNode|FieldDefinitionNode|InputObjectField|FieldDefinition $node,
    ): bool {
        $isList = false;

        if ($node instanceof InputObjectField || $node instanceof FieldDefinition) {
            $type = $node->getType();

            if ($type instanceof NonNull) {
                $type = $type->getWrappedType(false);
            }

            $isList = $type instanceof ListOfType;
        } else {
            $type = $node->type;

            if ($type instanceof NonNullTypeNode) {
                $type = $type->type;
            }

            $isList = $type instanceof ListTypeNode;
        }

        return $isList;
    }

    public function isTypeDefinitionExists(string $name): bool {
        try {
            return (bool) $this->getTypeDefinitionNode($name);
        } catch (Exception) {
            return false;
        }
    }

    public function getTypeDefinitionNode(
        Node|InputObjectField|FieldDefinition|string $node,
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
        // fixme(graphql): Is there any better way for this?
        return Parser::scalarTypeDefinition("scalar {$scalar}");
    }

    /**
     * @template T of \Nuwave\Lighthouse\Support\Contracts\Directive
     *
     * @param class-string<T> $class
     *
     * @return T|null
     */
    public function getNodeDirective(
        Node|TypeDefinitionNode|Type|InputObjectField|FieldDefinition $node,
        string $class,
    ): ?Directive {
        // todo(graphql): Seems there is no way to attach directive to \GraphQL\Type\Definition\Type?
        // todo(graphql): Should we throw an error if $node has multiple directives?
        return $node instanceof Node
            ? $this->getDirectives()->associatedOfType($node, $class)->first()
            : null;
    }

    public function getNodeTypeName(
        Node|Type|InputObjectField|FieldDefinition|TypeDefinitionNode|string $node,
    ): string {
        $name = null;

        if ($node instanceof Type || $node instanceof InputObjectField || $node instanceof FieldDefinition) {
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
        InputValueDefinitionNode|TypeDefinitionNode|FieldDefinitionNode|InputObjectField|FieldDefinition|Type $node,
    ): string {
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
    //</editor-fold>
}
