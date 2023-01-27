<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter;

use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Statistics;

/**
 * @internal
 */
abstract class Printed implements Statistics {
    public function __construct(
        protected Block $block,
    ) {
        // empty
    }

    public function __toString(): string {
        return (string) $this->block;
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
