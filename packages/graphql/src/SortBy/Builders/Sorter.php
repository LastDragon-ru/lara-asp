<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Builders;

use LastDragon_ru\LaraASP\GraphQL\Builder\Property;

/**
 * @template TBuilder of object
 */
interface Sorter {
    /**
     * @param TBuilder $builder
     *
     * @return TBuilder
     */
    public function sort(object $builder, Property $property, Direction $direction): object;
}
