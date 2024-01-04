<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Scout;

use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scout\FieldResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Direction;

class Builder {
    public function __construct(
        protected FieldResolver $fieldResolver,
    ) {
        // empty
    }

    public function handle(ScoutBuilder $builder, Property $property, Direction $direction): ScoutBuilder {
        $field   = $this->fieldResolver->getField($builder->model, $property);
        $builder = match ($direction) {
            Direction::asc  => $builder->orderBy($field, 'asc'),
            Direction::desc => $builder->orderBy($field, 'desc'),
        };

        return $builder;
    }
}
