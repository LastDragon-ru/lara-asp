<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter;

use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\Statistics;
use Stringable;

use function array_values;

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
     * @return array<string>
     */
    public function getUsedTypes(): array {
        return array_values($this->schema->getUsedTypes());
    }

    /**
     * @return array<string>
     */
    public function getUsedDirectives(): array {
        return array_values($this->schema->getUsedDirectives());
    }
}
