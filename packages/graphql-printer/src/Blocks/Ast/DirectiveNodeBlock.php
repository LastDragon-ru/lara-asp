<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Ast;

use GraphQL\Language\AST\DirectiveNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\NamedBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

use function mb_strlen;

/**
 * @internal
 */
class DirectiveNodeBlock extends Block implements NamedBlock {
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
            new ArgumentNodeList(
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
