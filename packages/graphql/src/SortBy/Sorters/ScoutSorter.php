<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Sorters;

use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\BuilderFieldResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Field;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\NotImplemented;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\Sorter;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Direction;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Nulls;
use Override;

/**
 * @implements Sorter<ScoutBuilder>
 */
class ScoutSorter implements Sorter {
    public function __construct(
        protected readonly BuilderFieldResolver $resolver,
    ) {
        // empty
    }

    #[Override]
    public function isNullsSupported(): bool {
        return false;
    }

    #[Override]
    public function sort(object $builder, Field $field, Direction $direction, Nulls $nulls = null): object {
        if ($nulls) {
            throw new NotImplemented('NULLs ordering');
        }

        $field   = $this->resolver->getField($builder, $field);
        $builder = match ($direction) {
            Direction::Asc, Direction::asc   => $builder->orderBy($field, 'asc'),
            Direction::Desc, Direction::desc => $builder->orderBy($field, 'desc'),
        };

        return $builder;
    }
}
