<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Sorters;

use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Direction;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Nulls;
use Override;

/**
 * @extends DatabaseSorter<QueryBuilder>
 */
class QuerySorter extends DatabaseSorter {
    #[Override]
    public function sort(object $builder, Property $property, Direction $direction, Nulls $nulls = null): object {
        $column  = $this->resolver->getProperty($builder, $property);
        $builder = $this->sortByColumn($builder, $column, $direction, $nulls);

        return $builder;
    }
}
