<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts;

use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Direction;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Nulls;

/**
 * @template TBuilder of object
 */
interface Sorter {
    /**
     * @param TBuilder $builder
     *
     * @return TBuilder
     */
    public function sort(object $builder, Property $property, Direction $direction, Nulls $nulls = null): object;
}
