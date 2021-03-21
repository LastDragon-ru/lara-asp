<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\ModelHelper;

use function array_keys;
use function count;
use function implode;
use function in_array;
use function is_array;
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
    public function build(EloquentBuilder|QueryBuilder $builder, array $clauses): EloquentBuilder|QueryBuilder {
        return $builder instanceof EloquentBuilder
            ? $this->process($builder, new SortStack($builder), $clauses)
            : $this->process($builder, null, $clauses);
    }
    // </editor-fold>

    // <editor-fold desc="Process">
    // =========================================================================
    /**
     * @param array<mixed> $clauses
     */
    protected function process(
        EloquentBuilder|QueryBuilder $builder,
        SortStack|null $stack,
        array $clauses,
    ): EloquentBuilder|QueryBuilder {
        foreach ((array) $clauses as $clause) {
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

            if (is_array($direction)) {
                $builder = $this->processRelation($builder, $stack, $column, $direction);
            } else {
                $builder = $this->processColumn($builder, $stack, $column, $direction);
            }
        }

        return $builder;
    }

    protected function processColumn(
        EloquentBuilder|QueryBuilder $builder,
        SortStack|null $stack,
        string $column,
        string $direction,
    ): EloquentBuilder|QueryBuilder {
        if ($stack && $builder instanceof EloquentBuilder) {
            if ($stack->hasTableAlias()) {
                if (!$builder->getQuery()->columns) {
                    $builder = $builder->addSelect($builder->qualifyColumn('*'));
                }

                $alias   = "{$stack->getTableAlias()}_{$column}";
                $aliased = "{$stack->getTableAlias()}.{$column} as {$alias}";
                $column  = $alias;

                if (!in_array($aliased, $builder->getQuery()->columns, true)) {
                    $builder = $builder->addSelect($aliased);
                }
            } else {
                $column = $builder->qualifyColumn($column);
            }
        }

        return $builder->orderBy($column, $direction);
    }

    /**
     * @param array<mixed> $clauses
     */
    protected function processRelation(
        EloquentBuilder|QueryBuilder $builder,
        SortStack|null $stack,
        string $name,
        array $clauses,
    ): EloquentBuilder {
        // QueryBuilder?
        if ($builder instanceof QueryBuilder) {
            throw new SortLogicException(sprintf(
                'Relation can not be used with `%s`.',
                QueryBuilder::class,
            ));
        }

        // Relation?
        $parentBuilder = $stack->getBuilder();
        $parentAlias   = $stack->getTableAlias();
        $relation      = (new ModelHelper($parentBuilder))->getRelation($name);
        $stack         = $stack->push($name, $relation->getRelated()->newQueryWithoutRelationships());

        if ($relation instanceof BelongsTo) {
            if (!$stack->hasTableAlias()) {
                $alias   = $relation->getRelationCountHash();
                $stack   = $stack->setTableAlias($alias);
                $table   = $relation->newModelInstance()->getTable();
                $builder = $builder->leftJoin(
                    "{$table} as {$alias}",
                    "{$alias}.{$relation->getOwnerKeyName()}",
                    '=',
                    $parentAlias
                        ? "{$parentAlias}.{$relation->getForeignKeyName()}"
                        : $relation->getQualifiedForeignKeyName(),
                );
            }
        } else {
            throw new SortLogicException(sprintf(
                'Relation of type `%s` cannot be used for sort, only `%s` supported.',
                $relation::class,
                implode('`, `', [
                    BelongsTo::class,
                ]),
            ));
        }

        // Return
        try {
            return $this->process($builder, $stack, [$clauses]);
        } finally {
            $stack->pop();
        }
    }
    // </editor-fold>
}
