<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Sorters;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Database\Query\Grammars\PostgresGrammar;
use Illuminate\Database\Query\Grammars\SQLiteGrammar;
use Illuminate\Database\Query\Grammars\SqlServerGrammar;
use Illuminate\Support\Str;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\BuilderFieldResolver;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\NotImplemented;
use LastDragon_ru\LaraASP\GraphQL\Package;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\Sorter;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Direction;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Nulls;
use Override;

use function count;
use function is_string;
use function mb_strtoupper;

/**
 * @template TBuilder of EloquentBuilder<EloquentModel>|QueryBuilder
 *
 * @implements Sorter<TBuilder>
 */
abstract class DatabaseSorter implements Sorter {
    public function __construct(
        protected readonly BuilderFieldResolver $resolver,
    ) {
        // empty
    }

    #[Override]
    public function isNullsSupported(): bool {
        return true;
    }

    /**
     * @param TBuilder                                           $builder
     * @param EloquentBuilder<EloquentModel>|QueryBuilder|string $column
     *
     * @return TBuilder
     */
    protected function sortByColumn(
        EloquentBuilder|QueryBuilder $builder,
        EloquentBuilder|QueryBuilder|string $column,
        Direction $direction,
        ?Nulls $nulls = null,
    ): EloquentBuilder|QueryBuilder {
        // Nulls?
        if ($nulls !== null && $nulls !== $this->getNullsDefault($builder, $direction)) {
            // `NULLS FIRST`/`NULLS LAST` supported?
            if ($this->isNullsOrderable($builder)) {
                // Well, it is easy...
                $order    = mb_strtoupper($direction->value);
                $bindings = [];
                $operator = match ($nulls) {
                    Nulls::First => 'NULLS FIRST',
                    Nulls::Last  => 'NULLS LAST',
                };

                if (!is_string($column)) {
                    $bindings = $column->getBindings();
                    $column   = "({$column->toSql()})";
                } else {
                    $column = $builder->getGrammar()->wrap($column);
                }

                $builder->orderByRaw(
                    "{$column} {$order} {$operator}",
                    $bindings,
                );
            } else {
                // If the sub query, we are includes it into `SELECT` to avoid
                // double execution. At least in MySQL, EXPLAIN shows two
                // "DEPENDENT SUBQUERY", see
                // * [Two subqueries in `ORDER BY`](https://www.db-fiddle.com/f/3jYWfCj3cXA1pM4U4VGH6S/0)
                // * [Subquery in `SELECT`](https://www.db-fiddle.com/f/nPrprqodmBMW5hCJ7Qin9V/0)
                //
                // Additional columns in `SELECT` may have side effects (they
                // will be returned and assigned to the model, the query will be
                // executed even if `reorder()` called, etc). But it should not
                // be a problem inside GraphQL query. Anyway, if someone has a
                // better idea of how we can handle it, you are welcome to
                // create pr/issue/discussion.
                if (!is_string($column)) {
                    // Something selected?
                    $query   = $builder instanceof EloquentBuilder ? $builder->getQuery() : $builder;
                    $columns = $query->columns ?? [];

                    if ($columns === []) {
                        $builder->addSelect('*');
                    }

                    // Select
                    $query  = $column;
                    $column = $this->getAlias($builder);

                    $builder->selectSub($query, $column);
                }

                // And then we just add additional clause to sort NULLs before
                // the column.
                //
                // It will be slow for big dataset, so would be good to use some
                // optimization. I see the only one way - use the additional
                // column which will store `IS NULL` status and an index for
                // that column. But it is the task for the future :)
                //
                // @see https://github.com/LastDragon-ru/php-packages/issues/21
                $wrapped  = $builder->getGrammar()->wrap($column);
                $operator = match ($nulls) {
                    Nulls::First => 'IS NOT NULL',
                    Nulls::Last  => 'IS NULL',
                };

                // `CASE WHEN` works everywhere... while `column IS (NOT) NULL`
                // doesn't work in SQL Server 🤷‍♂️
                $builder->orderByRaw("CASE WHEN {$wrapped} {$operator} THEN 1 ELSE 0 END");
                $builder->orderBy($column, $direction->value);
            }
        } else {
            $builder->orderBy($column, $direction->value);
        }

        // Return
        return $builder;
    }

    /**
     * @param EloquentBuilder<covariant EloquentModel>|QueryBuilder $builder
     */
    protected function getAlias(EloquentBuilder|QueryBuilder $builder): string {
        // We need some unique identifier to use it as an alias in the query.
        // The package and directive name with addition number-based suffix look
        // good. The number is the total count of `ORDER BY` clauses for the
        // current builder. It seems unique (for the builder) and should not
        // create conflicts while adding additional clauses 🤞
        $builder = $builder instanceof EloquentBuilder ? $builder->getQuery() : $builder;
        $count   = count($builder->orders ?? []) + count($builder->unionOrders ?? []);
        $alias   = Str::snake(Str::studly(Package::Name).'_'.Directive::Name).'__'.$count;

        return $alias;
    }

    /**
     * @param EloquentBuilder<covariant EloquentModel>|QueryBuilder $builder
     */
    protected function getNullsDefault(EloquentBuilder|QueryBuilder $builder, Direction $direction): Nulls {
        $grammar = $builder->getGrammar();
        $nulls   = match (true) {
            $grammar instanceof MySqlGrammar,
            $grammar instanceof SQLiteGrammar,
            $grammar instanceof SqlServerGrammar
                => $direction === Direction::Asc
                    ? Nulls::First
                    : Nulls::Last,
            $grammar instanceof PostgresGrammar
                => $direction === Direction::Asc
                    ? Nulls::Last
                    : Nulls::First,
            default
                => throw new NotImplemented('Default NULLs'),
        };

        return $nulls;
    }

    /**
     * @param EloquentBuilder<covariant EloquentModel>|QueryBuilder $builder
     */
    protected function isNullsOrderable(EloquentBuilder|QueryBuilder $builder): bool {
        // Technically support of `NULLS FIRST`/`NULLS LAST` was added to SQLite
        // in v3.30.0 which was released at 2019-10-04. But I'm not sure that
        // our min supported version of PHP ships with so old SQLite. This is
        // why the method always returns `true` for it.
        $grammar   = $builder->getGrammar();
        $orderable = $grammar instanceof PostgresGrammar
            || $grammar instanceof SQLiteGrammar;

        return $orderable;
    }
}
