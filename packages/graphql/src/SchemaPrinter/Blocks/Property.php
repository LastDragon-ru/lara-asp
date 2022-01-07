<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks;

use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;

/**
 * @internal
 */
class Property extends Block implements Named {
    public function __construct(
        Dispatcher $dispatcher,
        Settings $settings,
        private string $name,
        private Block $block,
    ) {
        parent::__construct($dispatcher, $settings, $block->getLevel(), $block->getUsed());
    }

    public function getName(): string {
        return $this->name;
    }

    public function isMultiline(): bool {
        return $this->getBlock()->isMultiline() || parent::isMultiline();
    }

    protected function getBlock(): Block {
        return $this->block;
    }

    protected function getSeparator(): string {
        return ':';
    }

    protected function content(): string {
        return "{$this->getName()}{$this->getSeparator()}{$this->space()}{$this->getBlock()}";
    }
}
