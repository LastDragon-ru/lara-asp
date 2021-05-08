<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts;

interface TypeProvider {
    public function getType(string $type, string $scalar = null, bool $nullable = null): string;
}
