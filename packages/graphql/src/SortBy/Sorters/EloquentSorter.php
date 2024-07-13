<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Sorters;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\JoinClause;
use LastDragon_ru\LaraASP\Eloquent\ModelHelper;
use LastDragon_ru\LaraASP\GraphQL\Builder\Field;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Direction;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Nulls;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\RelationUnsupported;
use Override;

use function array_shift;
use function class_exists;

/**
 * @extends DatabaseSorter<EloquentBuilder<Model>>
 */
class EloquentSorter extends DatabaseSorter {
    // <editor-fold desc="API">
    // =========================================================================
    #[Override]
    public function sort(object $builder, Field $field, Direction $direction, Nulls $nulls = null): object {
        // Column
        $relation = $field->getParent()->getPath();

        if ($relation) {
            $column = $field->getName();
            $column = $this->getRelationColumn($builder, $relation, $column, $direction);
        } else {
            $column = $this->resolver->getField($builder, $field);
        }

        // Order
        return $this->sortByColumn($builder, $column, $direction, $nulls);
    }
    // </editor-fold>

    // <editor-fold desc="Process">
    // =========================================================================
    /**
     * @param EloquentBuilder<Model>             $builder
     * @param non-empty-array<array-key, string> $relations
     *
     * @return EloquentBuilder<Model>
     */
    protected function getRelationColumn(
        EloquentBuilder $builder,
        array $relations,
        string $column,
        Direction $direction,
    ): EloquentBuilder {
        // Unfortunately `Builder::withAggregate()` doesn't supported nested
        // relations...
        $root     = array_shift($relations);
        $relation = $this->getRelation($builder, $root);
        $related  = $relation->getRelated();
        $query    = $relation
            ->getRelationExistenceQuery($related->newQuery(), $builder)
            ->mergeConstraintsFrom($relation->getQuery());
        $alias    = $related->getTable();
        $stack    = [$root];
        $index    = 0;
        $prefix   = $this->getAlias($builder);

        foreach ($relations as $name) {
            $stack[]  = $name;
            $current  = "{$prefix}__relation_{$index}";
            $relation = $this->getRelation($relation->getRelated()->newQuery(), $name, $stack);
            $query    = $this->joinRelation($query, $relation, $alias, $current);
            $alias    = $current;

            $index++;
        }

        // We need only one row
        $qualified = $this->resolver->getField($relation->getQuery(), new Field($alias, $column));
        $query     = $query->select($qualified)->reorder()->limit(1);
        $query     = $this->sortByColumn($query, $qualified, $direction);

        // Return
        return $query;
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    /**
     * @template T of Model
     *
     * @param EloquentBuilder<T> $builder
     * @param array<int, string> $stack
     *
     * @return Relation<T>
     */
    protected function getRelation(EloquentBuilder $builder, string $name, array $stack = []): Relation {
        return (new ModelHelper($builder))->getRelation($name);
    }

    /**
     * @template T of Model
     *
     * @param EloquentBuilder<T> $builder
     * @param Relation<T>        $relation
     *
     * @return EloquentBuilder<T>
     */
    protected function joinRelation(
        EloquentBuilder $builder,
        Relation $relation,
        string $parentAlias,
        string $currentAlias,
    ): EloquentBuilder {
        if ($relation instanceof BelongsTo) {
            $builder->joinSub(
                $relation->getQuery(),
                $currentAlias,
                "{$currentAlias}.{$relation->getOwnerKeyName()}",
                '=',
                $parentAlias
                    ? "{$parentAlias}.{$relation->getForeignKeyName()}"
                    : $relation->getQualifiedForeignKeyName(),
            );
        } elseif ($relation instanceof MorphOneOrMany) {
            $builder->joinSub(
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
        } elseif ($relation instanceof HasOneOrMany) {
            $builder->joinSub(
                $relation->getQuery(),
                $currentAlias,
                "{$currentAlias}.{$relation->getForeignKeyName()}",
                '=',
                $parentAlias
                    ? "{$parentAlias}.{$relation->getLocalKeyName()}"
                    : $relation->getQualifiedParentKeyName(),
            );
        } elseif (
            $relation instanceof HasManyThrough
            || ( // Since Laravel v11.15.0
                class_exists(HasOneOrManyThrough::class) && $relation instanceof HasOneOrManyThrough
            )
        ) {
            $builder->joinSub(
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
        } elseif ($relation instanceof BelongsToMany) {
            $builder->joinSub(
                $relation->getQuery(),
                $currentAlias,
                "{$currentAlias}.{$relation->getParentKeyName()}",
                '=',
                $parentAlias
                    ? "{$parentAlias}.{$relation->getRelatedKeyName()}"
                    : $relation->getQualifiedRelatedKeyName(),
            );
        } else {
            throw new RelationUnsupported($relation::class);
        }

        return $builder;
    }
    // </editor-fold>
}
