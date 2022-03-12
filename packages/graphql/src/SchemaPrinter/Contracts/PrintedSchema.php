<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts;

use Stringable;

interface PrintedSchema extends Statistics, Stringable {
    /**
     * @return array<string, string>
     */
    public function getUnusedTypes(): array;
}
