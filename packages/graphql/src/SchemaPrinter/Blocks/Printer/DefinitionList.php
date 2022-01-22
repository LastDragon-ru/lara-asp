<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Printer;

use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\BlockList;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;

/**
 * @internal
 */
class DefinitionList extends BlockList {
    public function __construct(
        Settings $settings,
        int $level,
        protected bool $indented = false,
    ) {
        parent::__construct(new Dispatcher(), $settings, $level);
    }

    protected function isWrapped(): bool {
        return true;
    }

    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeSchema();
    }

    protected function isAlwaysMultiline(): bool {
        return true;
    }

    protected function isIndented(): bool {
        return $this->indented;
    }

    protected function content(): string {
        $content = parent::content();

        if ($content && $this->isIndented()) {
            $content = "{$this->indent()}{$content}";
        }

        return $content;
    }
}
