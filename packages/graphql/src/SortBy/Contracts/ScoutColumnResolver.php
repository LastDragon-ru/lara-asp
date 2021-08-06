<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts;

/**
 * Convert property into Scout column.
 */
interface ScoutColumnResolver {
    /**
     * @param array<string> $path
     */
    public function getColumn(array $path): string;
}
