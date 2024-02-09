<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts;

use LastDragon_ru\LaraASP\GraphQL\Builder\Field;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Direction;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Nulls;

/**
 * @template TBuilder of object
 */
interface Sorter {
    /**
     * Should return `true` if Sorter can handle {@see Nulls}.
     */
    public function isNullsSupported(): bool;

    /**
     * @param TBuilder $builder
     *
     * @return TBuilder
     */
    public function sort(object $builder, Field $field, Direction $direction, Nulls $nulls = null): object;
}
