<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Sorters;

use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Field;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Direction;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Nulls;
use Override;

/**
 * @extends DatabaseSorter<QueryBuilder>
 */
class QuerySorter extends DatabaseSorter {
    #[Override]
    public function sort(object $builder, Field $field, Direction $direction, Nulls $nulls = null): object {
        $column  = $this->resolver->getField($builder, $field);
        $builder = $this->sortByColumn($builder, $column, $direction, $nulls);

        return $builder;
    }
}
