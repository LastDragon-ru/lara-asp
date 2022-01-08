<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks;

/**
 * @internal
 * @template TBlock of Block
 * @extends BlockList<TBlock>
 */
class ListBlockList extends BlockList {
    protected function getPrefix(): string {
        return '[';
    }

    protected function getSuffix(): string {
        return ']';
    }

    protected function getEmptyValue(): string {
        return "{$this->getPrefix()}{$this->getSuffix()}";
    }
}
