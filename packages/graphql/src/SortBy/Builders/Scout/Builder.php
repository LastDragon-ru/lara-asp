<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Scout;

use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;

use function implode;

class Builder {
    public function __construct(
        protected ?ColumnResolver $columnResolver = null,
    ) {
        // empty
    }

    public function handle(ScoutBuilder $builder, Property $property, string $direction): ScoutBuilder {
        // Column
        $path   = $property->getPath();
        $column = $this->columnResolver
            ? $this->columnResolver->getColumn($builder->model, $path)
            : implode('.', $path);

        // Order
        if ($direction) {
            $builder = $builder->orderBy($column, $direction);
        } else {
            $builder = $builder->orderBy($column);
        }

        return $builder;
    }
}
