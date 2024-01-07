<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Sorters;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\JoinClause;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Eloquent\ModelHelper;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\Sorter;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Direction;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Nulls;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\RelationUnsupported;
use LogicException;
use Override;

use function array_shift;
use function array_slice;
use function end;
use function implode;
use function is_a;

/**
 * @implements Sorter<EloquentBuilder<Model>>
 */
class EloquentSorter implements Sorter {
    /**
     * @var list<class-string<Relation<Model>>>
     */
    protected array $relations = [
        BelongsTo::class,
        BelongsToMany::class,
        HasOne::class,
        HasMany::class,
        MorphOne::class,
        MorphMany::class,
        MorphToMany::class,
        HasOneThrough::class,
        HasManyThrough::class,
    ];

    public function __construct() {
        // empty
    }

    // <editor-fold desc="API">
    // =========================================================================
    #[Override]
    public function isNullsSortable(): bool {
        return true;
    }

    #[Override]
    public function sort(object $builder, Property $property, Direction $direction, Nulls $nulls = null): object {
        // Column
        $path     = $property->getPath();
        $column   = Cast::toString(end($path));
        $relation = array_slice($path, 0, -1);

        if ($relation) {
            $column = $this->processRelation($builder, $relation, $column, $direction);
        }

        // Order
        return $this->processColumn($builder, $column, $direction);
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
    protected function processRelation(
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
        $current  = 'sort_by';

        foreach ($relations as $name) {
            $stack[]  = $name;
            $current  = "{$current}_{$name}";
            $relation = $this->getRelation($relation->getRelated()->newQuery(), $name, $stack);
            $query    = $this->joinRelation($query, $relation, $alias, $current);
            $alias    = $current;
        }

        // We need only one row
        $qualified = "{$alias}.{$column}";
        $query     = $query->select($qualified)->reorder()->limit(1);
        $query     = $this->processColumn($query, $qualified, $direction);

        // Return
        return $query;
    }

    /**
     * @param EloquentBuilder<Model>        $builder
     * @param EloquentBuilder<Model>|string $column
     *
     * @return EloquentBuilder<Model>
     */
    protected function processColumn(
        EloquentBuilder $builder,
        EloquentBuilder|string $column,
        Direction $direction,
    ): EloquentBuilder {
        return match ($direction) {
            Direction::Asc, Direction::asc   => $builder->orderBy($column, 'asc'),
            Direction::Desc, Direction::desc => $builder->orderBy($column, 'desc'),
        };
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
        } elseif ($relation instanceof HasOne || $relation instanceof HasMany) {
            $builder->joinSub(
                $relation->getQuery(),
                $currentAlias,
                "{$currentAlias}.{$relation->getForeignKeyName()}",
                '=',
                $parentAlias
                    ? "{$parentAlias}.{$relation->getLocalKeyName()}"
                    : $relation->getQualifiedParentKeyName(),
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
        } elseif ($relation instanceof HasManyThrough) {
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
            throw new LogicException('O_o => Please contact to developer.');
        }

        return $builder;
    }
    // </editor-fold>
}
