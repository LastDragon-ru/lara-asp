<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Scout;

use Illuminate\Database\Eloquent\Model;

/**
 * Convert nested property into Scout column.
 */
interface ColumnResolver {
    /**
     * @param array<string> $path
     */
    public function getColumn(Model $model, array $path): string;
}
