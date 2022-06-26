<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Query;

use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Clause;

use function implode;

class Builder {
    public function __construct() {
        // empty
    }

    /**
     * @param array<Clause> $clauses
     */
    public function handle(QueryBuilder $builder, array $clauses): QueryBuilder {
        foreach ($clauses as $clause) {
            // Column
            $path      = $clause->getPath();
            $column    = implode('.', $path);
            $direction = $clause->getDirection();

            // Order
            if ($direction) {
                $builder = $builder->orderBy($column, $direction);
            } else {
                $builder = $builder->orderBy($column);
            }
        }

        return $builder;
    }
}
