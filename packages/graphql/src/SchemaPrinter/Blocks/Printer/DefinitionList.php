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
        protected bool $schema = false,
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

    protected function isSchema(): bool {
        return $this->schema;
    }

    protected function content(): string {
        $content = parent::content();

        if ($content && $this->isSchema()) {
            $eof     = $this->getSettings()->getFileEnd();
            $content = "{$this->indent()}{$content}{$eof}";
        }

        return $content;
    }
}
