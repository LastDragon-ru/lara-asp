<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts;

interface Operator {
    public function getName(): string;

    /**
     * @param array<string, string> $map
     */
    public function getDefinition(array $map, string $scalar, bool $nullable): string;
}
