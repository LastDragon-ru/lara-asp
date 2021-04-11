<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL;

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\ScalarTypeDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;

abstract class AstManipulator {
    /**
     * Maps internal (operators) names to fully qualified names.
     *
     * @var array<string,array<string>>
     */
    protected array $map = [];

    public function __construct(
        protected DirectiveLocator $directives,
        protected DocumentAST $document,
    ) {
        $this->addRootTypeDefinitions();
    }

    // <editor-fold desc="Functions">
    // =========================================================================
    /**
     * @return array<string>
     */
    protected function getMap(object $owner): array {
        return $this->map[$owner::class] ?? [];
    }
    // </editor-fold>

    // <editor-fold desc="Defaults">
    // =========================================================================
    protected function addRootTypeDefinitions(): void {
        // empty
    }
    // </editor-fold>

    // <editor-fold desc="AST Helpers">
    // =========================================================================
    protected function isTypeDefinitionExists(string $name): bool {
        return (bool) $this->getTypeDefinitionNode($name);
    }

    protected function getTypeDefinitionNode(Node|string $node): ?TypeDefinitionNode {
        $type       = $node instanceof Node
            ? ASTHelper::getUnderlyingTypeName($node)
            : $node;
        $definition = $this->document->types[$type] ?? null;

        return $definition;
    }

    /**
     * @template T of \GraphQL\Language\AST\TypeDefinitionNode
     *
     * @param T $definition
     *
     * @return T
     */
    protected function addTypeDefinition(string $name, TypeDefinitionNode $definition): TypeDefinitionNode {
        if (!$this->isTypeDefinitionExists($name)) {
            $this->document->setTypeDefinition($definition);
        }

        return $this->getTypeDefinitionNode($name);
    }

    /**
     * @param array<\GraphQL\Language\AST\TypeDefinitionNode> $definitions
     */
    protected function addTypeDefinitions(object $owner, array $definitions): void {
        foreach ($definitions as $name => $definition) {
            $fullname                        = $definition->name->value;
            $this->map[$owner::class][$name] = $fullname;

            $this->addTypeDefinition($fullname, $definition);
        }
    }

    public function getScalarTypeNode(string $scalar): ScalarTypeDefinitionNode {
        // TODO [GraphQL] Is there any better way for this?
        return Parser::scalarTypeDefinition("scalar {$scalar}");
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
    //</editor-fold>
}
