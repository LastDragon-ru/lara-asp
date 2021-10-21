<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Eloquent;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\JoinClause;
use LastDragon_ru\LaraASP\Eloquent\ModelHelper;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Clause;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\RelationUnsupported;
use LogicException;

use function array_slice;
use function count;
use function implode;
use function in_array;
use function is_a;
use function reset;

class Builder {
    /**
     * @var array<class-string<Relation>>
     */
    protected array $relations = [
        BelongsTo::class,
        HasOne::class,
        MorphOne::class,
        HasOneThrough::class,
    ];

    public function __construct() {
        // empty
    }

    // <editor-fold desc="API">
    // =========================================================================
    /**
     * @param array<Clause> $clauses
     */
    public function handle(EloquentBuilder $builder, array $clauses): EloquentBuilder {
        return $this->process($builder, new Stack($builder), $clauses);
    }
    // </editor-fold>

    // <editor-fold desc="Process">
    // =========================================================================
    /**
     * @param array<Clause> $clauses
     */
    protected function process(EloquentBuilder $builder, Stack $stack, array $clauses): EloquentBuilder {
        foreach ($clauses as $clause) {
            // Column
            $path      = $clause->getPath();
            $column    = reset($path);
            $direction = $clause->getDirection();

            if (count($path) > 1) {
                $builder = $this->processRelation($builder, $stack, $column, $clause);
            } else {
                $builder = $this->processColumn($builder, $stack, $column, $direction);
            }
        }

        return $builder;
    }

    protected function processColumn(
        EloquentBuilder $builder,
        Stack $stack,
        string $column,
        ?string $direction,
    ): EloquentBuilder {
        // Add column
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

        // Order
        if ($direction) {
            $builder = $builder->orderBy($column, $direction);
        } else {
            $builder = $builder->orderBy($column);
        }

        // Return
        return $builder;
    }

    protected function processRelation(
        EloquentBuilder $builder,
        Stack $stack,
        string $name,
        Clause $clause,
    ): EloquentBuilder {
        // Relation?
        $parentBuilder = $stack->getBuilder();
        $parentAlias   = $stack->getTableAlias();
        $relation      = $this->getRelation($parentBuilder, $name, $stack);
        $stack         = $stack->push($name, $relation->getRelated()->newQueryWithoutRelationships());

        if (!$stack->hasTableAlias()) {
            $alias = $relation->getRelationCountHash();
            $stack = $stack->setTableAlias($alias);

            if ($relation instanceof BelongsTo) {
                $builder = $builder->leftJoinSub(
                    $relation->getQuery(),
                    $alias,
                    "{$alias}.{$relation->getOwnerKeyName()}",
                    '=',
                    $parentAlias
                        ? "{$parentAlias}.{$relation->getForeignKeyName()}"
                        : $relation->getQualifiedForeignKeyName(),
                );
            } elseif ($relation instanceof HasOne) {
                $builder = $builder->leftJoinSub(
                    $relation->getQuery(),
                    $alias,
                    "{$alias}.{$relation->getForeignKeyName()}",
                    '=',
                    $parentAlias
                        ? "{$parentAlias}.{$relation->getLocalKeyName()}"
                        : $relation->getQualifiedParentKeyName(),
                );
            } elseif ($relation instanceof MorphOne) {
                $builder = $builder->leftJoinSub(
                    $relation->getQuery(),
                    $alias,
                    static function (JoinClause $join) use ($relation, $alias, $parentAlias): void {
                        $join->on(
                            "{$alias}.{$relation->getForeignKeyName()}",
                            '=',
                            $parentAlias
                                ? "{$parentAlias}.{$relation->getLocalKeyName()}"
                                : $relation->getQualifiedParentKeyName(),
                        );
                        $join->where(
                            "{$alias}.{$relation->getMorphType()}",
                            '=',
                            $relation->getMorphClass(),
                        );
                    },
                );
            } elseif ($relation instanceof HasOneThrough) {
                $builder = $builder->leftJoinSub(
                    $relation->getQuery()->select([
                        "{$relation->getParent()->getQualifiedKeyName()} as {$alias}_id",
                        $relation->getRelated()->qualifyColumn('*'),
                    ]),
                    $alias,
                    "{$alias}.{$alias}_id",
                    '=',
                    $parentAlias
                        ? "{$parentAlias}.{$relation->getLocalKeyName()}"
                        : $relation->getQualifiedLocalKeyName(),
                );
            } else {
                throw new LogicException('O_o => Please contact to developer.');
            }
        }

        // Return
        try {
            $path      = array_slice($clause->getPath(), 1);
            $direction = $clause->getDirection();

            return $path
                ? $this->process($builder, $stack, [new Clause($path, $direction)])
                : $builder;
        } finally {
            $stack->pop();
        }
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    protected function getRelation(EloquentBuilder $builder, string $name, Stack $stack): Relation {
        $relation  = (new ModelHelper($builder))->getRelation($name);
        $supported = false;

        foreach ($this->relations as $class) {
            if (is_a($relation, $class)) {
                $supported = true;
                break;
            }
        }

        if (!$supported) {
            throw new RelationUnsupported(
                implode('.', [...$stack->getPath(), $name]),
                $relation::class,
                $this->relations,
            );
        }

        return $relation;
    }
    // </editor-fold>
}
