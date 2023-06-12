<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\DirectiveNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\NamedBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;

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
        private DirectiveNode $node,
    ) {
        parent::__construct($context, $level, $used);
    }

    public function getName(): string {
        return "@{$this->getNode()->name->value}";
    }

    public function getNode(): DirectiveNode {
        return $this->node;
    }

    protected function content(): string {
        // Print?
        if (!$this->isDirectiveAllowed($this->getNode()->name->value)) {
            return '';
        }

        // Convert
        $node = $this->getNode();
        $name = $this->getName();
        $used = mb_strlen($name);
        $args = $this->addUsed(
            new Arguments(
                $this->getContext(),
                $this->getLevel(),
                $this->getUsed() + $used,
                $node,
                $node->arguments,
            ),
        );

        // Statistics
        $this->addUsedDirective($name);

        // Return
        return "{$name}{$args}";
    }
}
