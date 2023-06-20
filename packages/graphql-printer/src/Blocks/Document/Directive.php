<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\DirectiveDefinitionNode;
use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\Directive as GraphQLDirective;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\NamedBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;

use function count;
use function mb_strlen;

/**
 * @internal
 */
#[GraphQLAstNode(DirectiveNode::class)]
class Directive extends Block implements NamedBlock {
    public function __construct(
        Context $context,
        int $level,
        int $used,
        private DirectiveNode $definition,
    ) {
        parent::__construct($context, $level, $used);
    }

    public function getName(): string {
        return "@{$this->getDefinition()->name->value}";
    }

    public function getDefinition(): DirectiveNode {
        return $this->definition;
    }

    protected function content(): string {
        // Print?
        if (!$this->isDirectiveAllowed($this->getDefinition()->name->value)) {
            return '';
        }

        // Convert
        $node = $this->getDefinition();
        $name = $this->getName();
        $used = mb_strlen($name);
        $args = $this->addUsed(
            new Arguments(
                $this->getContext(),
                $this->getLevel(),
                $this->getUsed() + $used,
                $node->arguments,
                $this->getTypes(),
            ),
        );

        // Statistics
        $this->addUsedDirective($name);

        // Return
        return "{$name}{$args}";
    }

    /**
     * @return array<string, (TypeNode&Node)|Type>
     */
    private function getTypes(): array {
        // Arguments?
        if (count($this->getDefinition()->arguments) <= 0) {
            return [];
        }

        // AST Node doesn't contain type of argument, but it can be
        // determined by Directive definition.
        $types      = [];
        $directive  = $this->getDefinition()->name->value;
        $definition = $this->getContext()->getDirective($directive);

        if ($definition instanceof DirectiveDefinitionNode) {
            foreach ($definition->arguments as $argument) {
                $types[$argument->name->value] = $argument->type;
            }
        } elseif ($definition instanceof GraphQLDirective) {
            foreach ($definition->args as $arg) {
                $types[$arg->name] = $arg->getType();
            }
        } else {
            // empty
        }

        return $types;
    }
}
