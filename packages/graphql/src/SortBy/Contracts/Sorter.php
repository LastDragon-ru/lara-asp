<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortBySorterDirective;

/**
 * @see SortBySorterDirective
 */
interface Sorter {
    /**
     * @template T of ScoutBuilder|EloquentBuilder|QueryBuilder
     *
     * @param T $builder
     *
     * @return T
     */
    public function sort(
        ScoutBuilder|EloquentBuilder|QueryBuilder $builder,
        string $property,
        string $direction,
    ): ScoutBuilder|EloquentBuilder|QueryBuilder;
}
