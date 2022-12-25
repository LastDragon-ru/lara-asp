<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Scout;

use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scout\FieldResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;

class Builder {
    public function __construct(
        protected FieldResolver $fieldResolver,
    ) {
        // empty
    }

    public function handle(ScoutBuilder $builder, Property $property, string $direction): ScoutBuilder {
        // Column
        $field = $this->fieldResolver->getField($builder->model, $property);

        // Order
        if ($direction) {
            $builder = $builder->orderBy($field, $direction);
        } else {
            $builder = $builder->orderBy($field);
        }

        return $builder;
    }
}
