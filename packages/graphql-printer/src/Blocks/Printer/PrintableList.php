<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Printer;

use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\DirectiveDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ListBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\DefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\ExtensionDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

use function rtrim;

/**
 * @internal
 * @extends ListBlock<Block>
 */
class PrintableList extends ListBlock {
    public function __construct(
        Context $context,
        int $level,
        protected bool $root = false,
    ) {
        parent::__construct($context, $level);
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
            $definition = $block->getBlock();

            if ($definition instanceof DefinitionBlock && !($definition instanceof ExtensionDefinitionBlock)) {
                $name = $definition->name();

                if ($name) {
                    if ($definition instanceof DirectiveDefinition) {
                        $this->addUsedDirective($name);
                    } else {
                        $this->addUsedType($name);
                    }
                }
            }
        }

        return $block;
    }
}
