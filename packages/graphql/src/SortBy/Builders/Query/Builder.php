<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Query;

use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;

use function implode;

class Builder {
    public function __construct() {
        // empty
    }

    public function handle(QueryBuilder $builder, Property $property, string $direction): QueryBuilder {
        // Column
        $path   = $property->getPath();
        $column = implode('.', $path);

        // Order
        if ($direction) {
            $builder = $builder->orderBy($column, $direction);
        } else {
            $builder = $builder->orderBy($column);
        }

        return $builder;
    }
}
