<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter;

use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\PrintedType;

/**
 * @internal
 */
class TypePrinted implements PrintedType {
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
