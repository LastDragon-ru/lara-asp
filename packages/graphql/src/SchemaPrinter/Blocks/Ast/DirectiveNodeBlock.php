<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Ast;

use GraphQL\Language\AST\DirectiveNode;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Events\DirectiveUsed;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Named;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;

use function mb_strlen;

/**
 * @internal
 */
class DirectiveNodeBlock extends Block implements Named {
    public function __construct(
        Dispatcher $dispatcher,
        Settings $settings,
        int $level,
        int $used,
        private DirectiveNode $node,
    ) {
        parent::__construct($dispatcher, $settings, $level, $used);
    }

    public function getName(): string {
        return $this->getNode()->name->value;
    }

    public function getNode(): DirectiveNode {
        return $this->node;
    }

    protected function content(): string {
        // Convert
        $at   = '@';
        $node = $this->getNode();
        $name = $this->getName();
        $used = mb_strlen($name) + mb_strlen($at);
        $args = new ArgumentNodeList(
            $this->getDispatcher(),
            $this->getSettings(),
            $this->getLevel(),
            $this->getUsed() + $used,
            $node->arguments,
        );

        // Event
        $this->getDispatcher()->notify(
            new DirectiveUsed($name),
        );

        // Return
        return "{$at}{$name}{$args}";
    }
}
