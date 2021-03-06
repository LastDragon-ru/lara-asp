<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;
use LastDragon_ru\LaraASP\GraphQL\Helpers\ModelHelper;
use LastDragon_ru\LaraASP\GraphQL\PackageTranslator;
use LogicException;

use function array_keys;
use function count;
use function implode;
use function in_array;
use function is_a;
use function is_array;
use function key;
use function reset;

class SortBuilder {
    /**
     * @var array<class-string<\Illuminate\Database\Eloquent\Relations\Relation>>
     */
    protected array $relations = [
        BelongsTo::class,
        HasOne::class,
        MorphOne::class,
        HasOneThrough::class,
    ];

    public function __construct(
        protected PackageTranslator $translator,
    ) {
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
                throw new SortLogicException($this->translator->get(
                    'sort_by.errors.empty_clause',
                ));
            }

            // More than one property?
            if (count($clause) > 1) {
                throw new SortLogicException($this->translator->get(
                    'sort_by.errors.too_many_properties',
                    [
                        'properties' => implode('`, `', array_keys($clause)),
                    ],
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
            throw new SortLogicException($this->translator->get(
                'sort_by.errors.unsupported_builder',
                [
                    'builder' => QueryBuilder::class,
                ],
            ));
        }

        // Relation?
        $parentBuilder = $stack->getBuilder();
        $parentAlias   = $stack->getTableAlias();
        $relation      = $this->getRelation($parentBuilder, $name);
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
            return $this->process($builder, $stack, [$clauses]);
        } finally {
            $stack->pop();
        }
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    protected function getRelation(EloquentBuilder $builder, string $name): Relation {
        $relation  = (new ModelHelper($builder))->getRelation($name);
        $supported = false;

        foreach ($this->relations as $class) {
            if (is_a($relation, $class)) {
                $supported = true;
                break;
            }
        }

        if (!$supported) {
            throw new SortLogicException($this->translator->get(
                'sort_by.errors.unsupported_relation',
                [
                    'relation'  => $relation::class,
                    'supported' => implode('`, `', $this->relations),
                ],
            ));
        }

        return $relation;
    }
    // </editor-fold>
}
