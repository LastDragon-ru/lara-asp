<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Query;

use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Direction;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Sorter;
use Override;

use function implode;

/**
 * @implements Sorter<QueryBuilder>
 */
class Builder implements Sorter {
    public function __construct() {
        // empty
    }

    #[Override]
    public function sort(object $builder, Property $property, Direction $direction): object {
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
            Direction::Asc, Direction::asc   => $builder->orderBy($column, 'asc'),
            Direction::Desc, Direction::desc => $builder->orderBy($column, 'desc'),
        };
    }
}
