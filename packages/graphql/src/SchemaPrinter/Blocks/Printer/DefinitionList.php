<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Printer;

use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\BlockList;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Misc\PrinterSettings;

use function rtrim;

/**
 * @internal
 * @extends BlockList<Block>
 */
class DefinitionList extends BlockList {
    public function __construct(
        PrinterSettings $settings,
        int $level,
        protected bool $schema = false,
    ) {
        parent::__construct($settings, $level);
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
            $content = rtrim($content);
            $content = "{$this->indent()}{$content}{$eof}";
        }

        return $content;
    }
}
