<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter;

use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\Statistics;
use Stringable;

class PrintedSchema implements Statistics, Stringable {
    public function __construct(
        protected Block $schema,
    ) {
        // empty
    }

    public function __toString(): string {
        return (string) $this->schema;
    }

    /**
     * @inheritDoc
     */
    public function getUsedTypes(): array {
        return $this->schema->getUsedTypes();
    }

    /**
     * @inheritDoc
     */
    public function getUsedDirectives(): array {
        return $this->schema->getUsedDirectives();
    }
}
