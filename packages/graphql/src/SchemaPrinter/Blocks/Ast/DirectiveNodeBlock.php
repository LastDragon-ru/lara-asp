<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Ast;

use GraphQL\Language\AST\DirectiveNode;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Named;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Misc\PrinterSettings;

use function mb_strlen;

/**
 * @internal
 */
class DirectiveNodeBlock extends Block implements Named {
    public function __construct(
        PrinterSettings $settings,
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
