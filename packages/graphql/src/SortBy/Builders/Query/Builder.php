<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Query;

use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Clause;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\BuilderUnsupported;

use function count;
use function reset;

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
            $column    = reset($path);
            $direction = $clause->getDirection();

            // Nested?
            if (count($path) > 1) {
                throw new BuilderUnsupported($builder::class);
            }

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
