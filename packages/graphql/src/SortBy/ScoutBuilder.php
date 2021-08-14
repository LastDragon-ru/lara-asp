<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy;

use Laravel\Scout\Builder;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\ScoutColumnResolver;

use function implode;

class ScoutBuilder {
    public function __construct(
        protected ?ScoutColumnResolver $columnResolver = null,
    ) {
        // empty
    }

    // <editor-fold desc="API">
    // =========================================================================
    /**
     * @param array<mixed> $clauses
     */
    public function build(Builder $builder, array $clauses): Builder {
        foreach ($clauses as $clause) {
            // Path
            $clause    = new SortClause($clause);
            $path      = [];
            $direction = null;

            do {
                $path[]    = $clause->getColumn();
                $direction = $clause->getDirection();
                $clause    = $clause->getChildClause();
            } while ($clause);

            // Column
            $column = $this->columnResolver
                ? $this->columnResolver->getColumn($builder->model, $path)
                : implode('.', $path);

            // Order
            if ($direction) {
                $builder->orderBy($column, $direction);
            } else {
                $builder->orderBy($column);
            }
        }

        return $builder;
    }
    // </editor-fold>
}
