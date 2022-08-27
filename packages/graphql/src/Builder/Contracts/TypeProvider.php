<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Contracts;

interface TypeProvider {
    /**
     * @param class-string<TypeDefinition> $definition
     */
    public function getType(string $definition, string $type = null, bool $nullable = null): string;
}
