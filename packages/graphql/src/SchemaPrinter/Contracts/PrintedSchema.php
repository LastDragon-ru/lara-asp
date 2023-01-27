<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts;

use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Statistics;
use Stringable;

interface PrintedSchema extends Statistics, Stringable {
    /**
     * @return array<string, string>
     */
    public function getUnusedTypes(): array;

    /**
     * @return array<string, string>
     */
    public function getUnusedDirectives(): array;
}
