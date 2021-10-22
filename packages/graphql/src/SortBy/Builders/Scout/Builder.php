<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Scout;

use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Clause;

use function implode;

class Builder {
    public function __construct(
        protected ?ColumnResolver $columnResolver = null,
    ) {
        // empty
    }

    /**
     * @param array<Clause> $clauses
     */
    public function handle(ScoutBuilder $builder, array $clauses): ScoutBuilder {
        foreach ($clauses as $clause) {
            // Column
            $path      = $clause->getPath();
            $direction = $clause->getDirection();
            $column    = $this->columnResolver
                ? $this->columnResolver->getColumn($builder->model, $path)
                : implode('.', $path);

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
