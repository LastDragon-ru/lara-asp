<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;

interface ComparisonOperator extends Operator {
    /**
     * @param EloquentBuilder<Model>|QueryBuilder $builder
     *
     * @return EloquentBuilder<Model>|QueryBuilder
     */
    public function apply(
        EloquentBuilder|QueryBuilder $builder,
        string $property,
        mixed $value,
    ): EloquentBuilder|QueryBuilder;
}
