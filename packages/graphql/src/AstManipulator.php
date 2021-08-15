<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL;

use Exception;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Type\Definition\Type;
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

    protected function getTypeDefinitionNode(Node|string $node): TypeDefinitionNode|Type {
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
    protected function getNodeDirective(Node $node, string $class): ?object {
        return $this->directives->associatedOfType($node, $class)->first();
    }

    protected function getNodeTypeName(Node|string $node): string {
        return $node instanceof Node
            ? ASTHelper::getUnderlyingTypeName($node)
            : $node;
    }

    public function getNodeName(InputValueDefinitionNode|TypeDefinitionNode|Type $node): string {
        return $node instanceof TypeDefinitionNode || $node instanceof InputValueDefinitionNode
            ? $node->name->value
            : $node->name;
    }
    //</editor-fold>
}
