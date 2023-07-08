<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types;

use GraphQL\Language\AST\DefinitionNode;
use GraphQL\Language\AST\Node;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\DirectiveDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Factory;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ListBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;

/**
 * @internal
 * @extends ListBlock<Block, array-key, DefinitionNode&Node>
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

    protected function block(int|string $key, mixed $item): Block {
        return Factory::create($this->getContext(), $item);
    }

    protected function analyze(Collector $collector, Block $block): Block {
        $block = parent::analyze($collector, $block);

        if ($block instanceof DefinitionBlock && !($block instanceof ExtensionDefinitionBlock)) {
            $name = $block->name();

            if ($name) {
                if ($block instanceof DirectiveDefinition) {
                    $collector->addUsedDirective($name);
                } else {
                    $collector->addUsedType($name);
                }
            }
        }

        return $block;
    }
}
