<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Misc;

use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Result;

/**
 * @internal
 */
class ResultImpl implements Result {
    public function __construct(
        protected int $level,
        protected Block $block,
    ) {
        // empty
    }

    public function __toString(): string {
        return $this->block->serialize($this->level, 0);
    }

    /**
     * @inheritDoc
     */
    public function getUsedTypes(): array {
        return $this->block->getUsedTypes();
    }

    /**
     * @inheritDoc
     */
    public function getUsedDirectives(): array {
        return $this->block->getUsedDirectives();
    }
}
