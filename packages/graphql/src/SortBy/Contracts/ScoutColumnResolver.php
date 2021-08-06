<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Convert property into Scout column.
 */
interface ScoutColumnResolver {
    /**
     * @param array<string> $path
     */
    public function getColumn(Model $model, array $path): string;
}
