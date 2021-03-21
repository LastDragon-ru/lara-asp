<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

use function array_keys;
use function count;
use function implode;
use function key;
use function reset;
use function sprintf;

class SortBuilder {
    public function __construct() {
        // empty
    }

    // <editor-fold desc="API">
    // =========================================================================
    /**
     * @param array<mixed> $clauses
     */
    public function build(QueryBuilder|EloquentBuilder $builder, array $clauses): QueryBuilder|EloquentBuilder {
        return $this->process($builder, $clauses);
    }
    // </editor-fold>

    // <editor-fold desc="Process">
    // =========================================================================
    /**
     * @param array<mixed> $clauses
     */
    protected function process(QueryBuilder|EloquentBuilder $builder, array $clauses): QueryBuilder|EloquentBuilder {
        foreach ((array) $clauses as $clause) {
            $builder = $this->processClause($builder, $clause);
        }

        return $builder;
    }

    /**
     * @param array<string, string> $clause
     */
    protected function processClause(
        EloquentBuilder|QueryBuilder $builder,
        array $clause,
    ): QueryBuilder|EloquentBuilder {
        // Empty?
        if (!$clause) {
            throw new SortLogicException(
                'Sort clause cannot be empty.',
            );
        }

        // More than one property?
        if (count($clause) > 1) {
            throw new SortLogicException(sprintf(
                'Only one property allowed, found: %s.',
                '`'.implode('`, `', array_keys($clause)).'`',
            ));
        }

        // Apply
        $direction = reset($clause);
        $column    = key($clause);

        $builder->orderBy($column, $direction);

        // Return
        return $builder;
    }
    // </editor-fold>
}
