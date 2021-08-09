<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL;

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Type\Definition\Type;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\TypeRegistry;

use function sprintf;

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
        return (bool) $this->getTypeDefinitionNode($name);
    }

    protected function getTypeDefinitionNode(Node|string $node): TypeDefinitionNode|Type|null {
        $name       = $this->getNodeTypeName($node);
        $definition = $this->document->types[$name] ?? null;

        if ($this->types->has($name)) {
            $definition = $this->types->get($name);
        }

        return $definition;
    }

    /**
     * @template T of \GraphQL\Language\AST\TypeDefinitionNode
     *
     * @param T $definition
     *
     * @return T
     */
    protected function addTypeDefinition(TypeDefinitionNode $definition): TypeDefinitionNode {
        $name = $this->getNodeName($definition);

        if ($this->isTypeDefinitionExists($name)) {
            throw new PackageException(sprintf(
                'Type Definition `%s` already defined.',
                $name,
            ));
        }

        $this->document->setTypeDefinition($definition);

        return $definition;
    }

    /**
     * @template T
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

    protected function getNodeName(TypeDefinitionNode|Type $node): string {
        return $node instanceof TypeDefinitionNode
            ? $node->name->value
            : $node->name;
    }
    //</editor-fold>
}
