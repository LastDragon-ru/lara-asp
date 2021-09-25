<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Scout;

use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Clause;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\ScoutColumnResolver;

use function implode;

class Builder {
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
    public function handle(ScoutBuilder $builder, array $clauses): ScoutBuilder {
        foreach ($clauses as $clause) {
            // Path
            $clause    = new Clause($clause);
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
