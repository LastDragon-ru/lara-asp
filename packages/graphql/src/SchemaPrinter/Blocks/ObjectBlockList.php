<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks;

/**
 * @internal
 * @template TBlock of Block
 * @extends ListBlock<TBlock>
 */
class ObjectBlockList extends ListBlock {
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
