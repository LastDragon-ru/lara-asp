<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Ast;

use GraphQL\Language\AST\DirectiveNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\NamedBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;

use function mb_strlen;

/**
 * @internal
 */
class DirectiveNodeBlock extends Block implements NamedBlock {
    public function __construct(
        Settings $settings,
        int $level,
        int $used,
        private DirectiveNode $node,
    ) {
        parent::__construct($settings, $level, $used);
    }

    public function getName(): string {
        return "@{$this->getNode()->name->value}";
    }

    public function getNode(): DirectiveNode {
        return $this->node;
    }

    protected function content(): string {
        // Convert
        $node = $this->getNode();
        $name = $this->getName();
        $used = mb_strlen($name);
        $args = $this->addUsed(
            new ArgumentNodeList(
                $this->getSettings(),
                $this->getLevel(),
                $this->getUsed() + $used,
                $node->arguments,
            ),
        );

        // Statistics
        $this->addUsedDirective($name);

        // Return
        return "{$name}{$args}";
    }
}
