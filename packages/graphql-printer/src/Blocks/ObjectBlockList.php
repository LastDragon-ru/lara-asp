<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks;

use Override;

/**
 * @internal
 * @template TBlock of Block
 * @template TKey of array-key
 * @template TItem
 * @extends ListBlock<TBlock, TKey, TItem>
 */
abstract class ObjectBlockList extends ListBlock {
    #[Override]
    protected function getPrefix(): string {
        return '{';
    }

    #[Override]
    protected function getSuffix(): string {
        return '}';
    }

    #[Override]
    protected function isAlwaysMultiline(): bool {
        return true;
    }
}
