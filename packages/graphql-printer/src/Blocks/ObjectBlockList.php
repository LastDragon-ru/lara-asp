<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks;

/**
 * @internal
 * @template TBlock of Block
 * @template TKey of array-key
 * @template TItem
 * @extends ListBlock<TBlock, TKey, TItem>
 */
abstract class ObjectBlockList extends ListBlock {
    protected function getPrefix(): string {
        return '{';
    }

    protected function getSuffix(): string {
        return '}';
    }

    protected function isAlwaysMultiline(): bool {
        return true;
    }
}
