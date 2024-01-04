<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Query;

use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Direction;

use function implode;

class Builder {
    public function __construct() {
        // empty
    }

    public function handle(QueryBuilder $builder, Property $property, Direction $direction): QueryBuilder {
        $path    = $property->getPath();
        $column  = implode('.', $path);
        $builder = $this->processColumn($builder, $column, $direction);

        return $builder;
    }

    protected function processColumn(
        QueryBuilder $builder,
        QueryBuilder|string $column,
        Direction $direction,
    ): QueryBuilder {
        return match ($direction) {
            Direction::asc  => $builder->orderBy($column, 'asc'),
            Direction::desc => $builder->orderBy($column, 'desc'),
        };
    }
}
