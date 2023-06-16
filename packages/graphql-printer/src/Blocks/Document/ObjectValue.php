<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ListBlock;

/**
 * @internal
 * @template TBlock of Block
 * @extends ListBlock<TBlock>
 */
class ObjectValue extends ListBlock {
    protected function getPrefix(): string {
        return '{';
    }

    protected function getSuffix(): string {
        return '}';
    }

    protected function getEmptyValue(): string {
        return "{$this->getPrefix()}{$this->getSuffix()}";
    }

    protected function isAlwaysMultiline(): bool {
        return true;
    }

    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeArguments();
    }
}
