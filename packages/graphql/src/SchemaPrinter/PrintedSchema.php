<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter;

use Stringable;

class PrintedSchema implements Stringable {
    public function __construct(
        protected string $schema,
    ) {
        // empty
    }

    public function __toString(): string {
        return $this->schema;
    }
}
