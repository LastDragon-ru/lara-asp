<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Contracts;

interface TypeProvider {
    /**
     * @param class-string<TypeDefinition> $type
     */
    public function getType(string $type, string $scalar = null, bool $nullable = null): string;
}
