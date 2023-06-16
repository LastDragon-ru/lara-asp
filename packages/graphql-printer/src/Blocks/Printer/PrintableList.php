<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Printer;

use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\DefinitionList;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

use function rtrim;

/**
 * @internal
 */
class PrintableList extends DefinitionList {
    public function __construct(
        Context $context,
        int $level,
        protected bool $root = false,
    ) {
        parent::__construct($context, $level);
    }

    protected function isRoot(): bool {
        return $this->root;
    }

    protected function content(): string {
        $content = parent::content();

        if ($content && $this->isRoot()) {
            $eof     = $this->getSettings()->getFileEnd();
            $content = rtrim($content);
            $content = "{$this->indent()}{$content}{$eof}";
        }

        return $content;
    }

    protected function analyze(Block $block): Block {
        $block = parent::analyze($block);

        if ($block instanceof PrintableBlock) {
            parent::analyze($block->getBlock());
        }

        return $block;
    }
}
