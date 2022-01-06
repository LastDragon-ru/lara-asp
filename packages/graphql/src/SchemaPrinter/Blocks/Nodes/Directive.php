<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Nodes;

use GraphQL\Language\AST\DirectiveNode;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;

use function mb_strlen;

/**
 * @internal
 */
class Directive extends Block {
    public function __construct(
        Dispatcher $dispatcher,
        Settings $settings,
        int $level,
        int $used,
        private DirectiveNode $node,
    ) {
        parent::__construct($dispatcher, $settings, $level, $used);
    }

    public function getNode(): DirectiveNode {
        return $this->node;
    }

    protected function content(): string {
        $node = $this->getNode();
        $name = "@{$node->name->value}";
        $used = mb_strlen($name);
        $args = new Arguments(
            $this->getDispatcher(),
            $this->getSettings(),
            $this->getLevel(),
            $this->getUsed() + $used,
            $node->arguments,
        );

        return "{$name}{$args}";
    }
}
