<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Sorters;

use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\Sorter;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Direction;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Nulls;
use Override;

use function implode;

/**
 * @implements Sorter<QueryBuilder>
 */
class QuerySorter implements Sorter {
    public function __construct() {
        // empty
    }

    #[Override]
    public function isNullsSortable(): bool {
        return true;
    }

    #[Override]
    public function sort(object $builder, Property $property, Direction $direction, Nulls $nulls = null): object {
        $path    = $property->getPath();
        $column  = implode('.', $path);
        $builder = $this->processColumn($builder, $column, $direction);

        return $builder;
    }

    protected function processColumn(
        QueryBuilder $builder,
        QueryBuilder|string $column,
        Direction $direction,
    ): QueryBuilder {
        return match ($direction) {
            Direction::Asc, Direction::asc   => $builder->orderBy($column, 'asc'),
            Direction::Desc, Direction::desc => $builder->orderBy($column, 'desc'),
        };
    }
}
