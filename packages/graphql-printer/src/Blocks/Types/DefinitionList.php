<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types;

use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\DirectiveDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ListBlock;

/**
 * @internal
 * @extends ListBlock<Block>
 */
class DefinitionList extends ListBlock {
    protected function isWrapped(): bool {
        return true;
    }

    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeSchema();
    }

    protected function isAlwaysMultiline(): bool {
        return true;
    }

    protected function analyze(Block $block): Block {
        $block = parent::analyze($block);

        if ($block instanceof DefinitionBlock && !($block instanceof ExtensionDefinitionBlock)) {
            $name = $block->name();

            if ($name) {
                if ($block instanceof DirectiveDefinition) {
                    $this->addUsedDirective($name);
                } else {
                    $this->addUsedType($name);
                }
            }
        }

        return $block;
    }
}
