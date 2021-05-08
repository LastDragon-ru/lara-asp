<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\SearchBuilder;

interface LogicalOperator extends Operator {
    /**
     * @param array<mixed> $conditions
     */
    public function apply(
        SearchBuilder $search,
        EloquentBuilder|QueryBuilder $builder,
        array $conditions,
        ?string $tableAlias,
    ): EloquentBuilder|QueryBuilder;
}
