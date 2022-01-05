<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Printer\Blocks\Nodes;

use GraphQL\Language\AST\DirectiveNode;
use LastDragon_ru\LaraASP\GraphQL\Printer\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\Printer\Settings;

use function mb_strlen;

/**
 * @internal
 */
class Directive extends Block {
    public function __construct(
        Settings $settings,
        int $level,
        int $used,
        private DirectiveNode $directive,
    ) {
        parent::__construct($settings, $level, $used);
    }

    protected function content(): string {
        $name = "@{$this->directive->name->value}";
        $used = mb_strlen($name);
        $args = new Arguments(
            $this->getSettings(),
            $this->getLevel(),
            $this->getUsed() + $used,
            $this->directive->arguments,
        );

        return "{$name}{$args}";
    }
}
