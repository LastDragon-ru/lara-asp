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

use function array_shift;
use function array_slice;
use function end;
use function implode;
use function is_a;

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
        foreach ($clauses as $clause) {
            // Column
            $path      = $clause->getPath();
            $column    = end($path);
            $relation  = array_slice($path, 0, -1);
            $direction = $clause->getDirection();

            if ($relation) {
                $column = $this->processRelation($builder, $relation, $column);
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
    // </editor-fold>

    // <editor-fold desc="Process">
    // =========================================================================
    /**
     * @param non-empty-array<string> $relations
     */
    protected function processRelation(EloquentBuilder $builder, array $relations, string $column): EloquentBuilder {
        // Unfortunately `Builder::withAggregate()` doesn't supported nested
        // relations...
        $root     = array_shift($relations);
        $relation = $this->getRelation($builder, $root);
        $related  = $relation->getRelated();
        $query    = $relation
            ->getRelationExistenceQuery($related->newQuery(), $builder)
            ->mergeConstraintsFrom($relation->getQuery())
            ->select($related->qualifyColumn($column))
            ->reorder()
            ->limit(1);
        $alias    = $related->getTable();
        $stack    = [$root];

        foreach ($relations as $name) {
            $stack[]  = $name;
            $current  = "sort_by_{$name}";
            $relation = $this->getRelation($relation->getRelated()->newQuery(), $name, $stack);
            $query    = $this->joinRelation($query, $relation, $alias, $current);
            $alias    = $current;
        }

        return $query;
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    /**
     * @param array<string> $stack
     */
    protected function getRelation(EloquentBuilder $builder, string $name, array $stack = []): Relation {
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
                implode('.', [...$stack, $name]),
                $relation::class,
                $this->relations,
            );
        }

        return $relation;
    }

    protected function joinRelation(
        EloquentBuilder $builder,
        Relation $relation,
        string $parentAlias,
        string $currentAlias,
    ): EloquentBuilder {
        if ($relation instanceof BelongsTo) {
            $builder = $builder->joinSub(
                $relation->getQuery(),
                $currentAlias,
                "{$currentAlias}.{$relation->getOwnerKeyName()}",
                '=',
                $parentAlias
                    ? "{$parentAlias}.{$relation->getForeignKeyName()}"
                    : $relation->getQualifiedForeignKeyName(),
            );
        } elseif ($relation instanceof HasOne) {
            $builder = $builder->joinSub(
                $relation->getQuery(),
                $currentAlias,
                "{$currentAlias}.{$relation->getForeignKeyName()}",
                '=',
                $parentAlias
                    ? "{$parentAlias}.{$relation->getLocalKeyName()}"
                    : $relation->getQualifiedParentKeyName(),
            );
        } elseif ($relation instanceof MorphOne) {
            $builder = $builder->joinSub(
                $relation->getQuery(),
                $currentAlias,
                static function (JoinClause $join) use ($relation, $currentAlias, $parentAlias): void {
                    $join->on(
                        "{$currentAlias}.{$relation->getForeignKeyName()}",
                        '=',
                        $parentAlias
                            ? "{$parentAlias}.{$relation->getLocalKeyName()}"
                            : $relation->getQualifiedParentKeyName(),
                    );
                    $join->where(
                        "{$currentAlias}.{$relation->getMorphType()}",
                        '=',
                        $relation->getMorphClass(),
                    );
                },
            );
        } elseif ($relation instanceof HasOneThrough) {
            $builder = $builder->joinSub(
                $relation->getQuery()->select([
                    "{$relation->getParent()->getQualifiedKeyName()} as {$currentAlias}_key",
                    $relation->getRelated()->qualifyColumn('*'),
                ]),
                $currentAlias,
                "{$currentAlias}.{$currentAlias}_key",
                '=',
                $parentAlias
                    ? "{$parentAlias}.{$relation->getLocalKeyName()}"
                    : $relation->getQualifiedLocalKeyName(),
            );
        } else {
            throw new LogicException('O_o => Please contact to developer.');
        }

        return $builder;
    }
    // </editor-fold>
}
