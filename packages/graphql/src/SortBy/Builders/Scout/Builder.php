<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Scout;

use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scout\FieldResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Direction;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Sorter;
use Override;

/**
 * @implements Sorter<ScoutBuilder>
 */
class Builder implements Sorter {
    public function __construct(
        protected FieldResolver $fieldResolver,
    ) {
        // empty
    }

    #[Override]
    public function sort(object $builder, Property $property, Direction $direction): object {
        $field   = $this->fieldResolver->getField($builder->model, $property);
        $builder = match ($direction) {
            Direction::Asc, Direction::asc   => $builder->orderBy($field, 'asc'),
            Direction::Desc, Direction::desc => $builder->orderBy($field, 'desc'),
        };

        return $builder;
    }
}
