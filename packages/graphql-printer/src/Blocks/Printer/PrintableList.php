<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Printer;

use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\DefinitionList;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

use function rtrim;

/**
 * @internal
 */
class PrintableList extends DefinitionList {
    public function __construct(
        Context $context,
        protected bool $root = false,
    ) {
        parent::__construct($context);
    }

    protected function isRoot(): bool {
        return $this->root;
    }

    protected function content(Collector $collector, int $level, int $used): string {
        $content = parent::content($collector, $level, $used);

        if ($content && $this->isRoot()) {
            $eof     = $this->getSettings()->getFileEnd();
            $content = rtrim($content);
            $content = "{$this->indent($level)}{$content}{$eof}";
        }

        return $content;
    }

    protected function analyze(Collector $collector, Block $block): Block {
        $block = parent::analyze($collector, $block);

        if ($block instanceof PrintableBlock) {
            parent::analyze($collector, $block->getBlock());
        }

        return $block;
    }
}
