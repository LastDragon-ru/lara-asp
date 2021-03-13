<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical;

use Closure;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

interface LogicalOperator {
    public function apply(EloquentBuilder|QueryBuilder $builder, Closure $nested): EloquentBuilder|QueryBuilder;
}
