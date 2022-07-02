<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL;

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
        protected DirectiveLocator $directives,
        protected DocumentAST $document,
        protected TypeRegistry $types,
    ) {
        // empty
    }

    // <editor-fold desc="AST Helpers">
    // =========================================================================
    protected function isPlaceholder(Node|InputObjectField|string $node): bool {
        // Lighthouse uses `_` type as a placeholder for directives like `@orderBy`
        return $this->getNodeTypeName($node) === '_';
    }

    protected function isList(
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

    protected function isTypeDefinitionExists(string $name): bool {
        try {
            return (bool) $this->getTypeDefinitionNode($name);
        } catch (Exception) {
            return false;
        }
    }

    protected function getTypeDefinitionNode(
        Node|InputObjectField|FieldDefinition|string $node,
    ): TypeDefinitionNode|Type {
        $name       = $this->getNodeTypeName($node);
        $definition = $this->document->types[$name] ?? null;

        if (!$definition) {
            $definition = Type::getStandardTypes()[$name] ?? null;
        }

        if (!$definition && $this->types->has($name)) {
            $definition = $this->types->get($name);
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
    protected function addTypeDefinition(TypeDefinitionNode $definition): TypeDefinitionNode {
        $name = $this->getNodeName($definition);

        if ($this->isTypeDefinitionExists($name)) {
            throw new TypeDefinitionAlreadyDefined($name);
        }

        $this->document->setTypeDefinition($definition);

        return $definition;
    }

    protected function removeTypeDefinition(string $name): void {
        if (!$this->isTypeDefinitionExists($name)) {
            throw new TypeDefinitionUnknown($name);
        }

        // Remove
        unset($this->document->types[$name]);
    }

    /**
     * @template T of \Nuwave\Lighthouse\Support\Contracts\Directive
     *
     * @param class-string<T> $class
     *
     * @return T|null
     */
    protected function getNodeDirective(Node|Type|InputObjectField|FieldDefinition $node, string $class): ?Directive {
        // TODO [graphql] Seems there is no way to attach directive to \GraphQL\Type\Definition\Type?
        return $node instanceof Node
            ? $this->directives->associatedOfType($node, $class)->first()
            : null;
    }

    protected function getNodeTypeName(
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

    protected function getNodeName(
        InputValueDefinitionNode|TypeDefinitionNode|FieldDefinitionNode|InputObjectField|FieldDefinition|Type $node,
    ): string {
        $name = $node->name;

        if ($name instanceof NameNode) {
            $name = $name->value;
        }

        return $name;
    }

    public function getNodeTypeFullName(
        Node|Type|InputObjectField|FieldDefinition|string $node,
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
