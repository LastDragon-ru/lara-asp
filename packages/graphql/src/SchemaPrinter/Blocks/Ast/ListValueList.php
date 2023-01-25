<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Ast;

use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\ListBlock;

/**
 * @internal
 * @template TBlock of Block
 * @extends ListBlock<TBlock>
 */
class ListValueList extends ListBlock {
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
