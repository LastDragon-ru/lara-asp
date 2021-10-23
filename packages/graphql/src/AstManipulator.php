<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL;

use Exception;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\WrappingType;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\TypeDefinitionAlreadyDefined;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\TypeDefinitionUnknown;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\TypeRegistry;

abstract class AstManipulator {
    public function __construct(
        protected DirectiveLocator $directives,
        protected DocumentAST $document,
        protected TypeRegistry $types,
    ) {
        $this->addDefaultTypeDefinitions();
    }

    // <editor-fold desc="Defaults">
    // =========================================================================
    protected function addDefaultTypeDefinitions(): void {
        // empty
    }
    // </editor-fold>

    // <editor-fold desc="AST Helpers">
    // =========================================================================
    protected function isTypeDefinitionExists(string $name): bool {
        try {
            return (bool) $this->getTypeDefinitionNode($name);
        } catch (Exception) {
            return false;
        }
    }

    protected function getTypeDefinitionNode(Node|InputObjectField|string $node): TypeDefinitionNode|Type {
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

    /**
     * @template T of \Nuwave\Lighthouse\Support\Contracts\Directive
     *
     * @param class-string<T> $class
     *
     * @return T|null
     */
    protected function getNodeDirective(Node|Type|InputObjectField $node, string $class): ?object {
        // TODO [graphql] Seems there is no way to attach directive to \GraphQL\Type\Definition\Type?
        return $node instanceof Node
            ? $this->directives->associatedOfType($node, $class)->first()
            : null;
    }

    protected function getNodeTypeName(Node|InputObjectField|string $node): string {
        $name = null;

        if ($node instanceof InputObjectField) {
            $type = $node->getType();

            if ($type instanceof WrappingType) {
                $name = $type->getWrappedType(true)->name;
            } else {
                $name = $type->name;
            }
        } elseif ($node instanceof Node) {
            $name = ASTHelper::getUnderlyingTypeName($node);
        } else {
            $name = $node;
        }

        return $name;
    }

    public function getNodeName(InputValueDefinitionNode|TypeDefinitionNode|InputObjectField|Type $node): string {
        return $node instanceof TypeDefinitionNode || $node instanceof InputValueDefinitionNode
            ? $node->name->value
            : $node->name;
    }

    protected function copyFieldToType(
        InputObjectTypeDefinitionNode $type,
        InputValueDefinitionNode|InputObjectField $field,
        string $newFieldType,
        string $newFieldDescription,
    ): bool {
        $newField = null;

        if ($field instanceof InputValueDefinitionNode) {
            $clone = $field->cloneDeep();

            if ($clone instanceof InputValueDefinitionNode) {
                $clone->type        = Parser::typeReference($newFieldType);
                $clone->description = Parser::description("\"\"\"{$newFieldDescription}\"\"\"");
                $newField           = $clone;
            }
        } else {
            $newField = Parser::inputValueDefinition(
                <<<DEF
                """
                {$newFieldDescription}
                """
                {$field->name}: {$newFieldType}
                DEF,
            );
        }

        if ($newField) {
            $type->fields[] = $newField;
        }

        return (bool) $newField;
    }
    //</editor-fold>
}
