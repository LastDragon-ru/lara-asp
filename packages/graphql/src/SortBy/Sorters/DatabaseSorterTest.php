<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Sorters;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\BuilderPropertyResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Direction;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Nulls;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\Car;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\User;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\UnknownValue;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function is_string;

/**
 * @internal
 */
#[CoversClass(DatabaseSorter::class)]
class DatabaseSorterTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderSortByColumn
     *
     * @param array{query: string, bindings: array<array-key, mixed>}               $expected
     * @param Closure(static): (EloquentBuilder<EloquentModel>|QueryBuilder)        $builderFactory
     * @param Closure(static): (EloquentBuilder<EloquentModel>|QueryBuilder)|string $columnFactory
     */
    public function testSortByColumn(
        array $expected,
        Closure $builderFactory,
        Closure|string $columnFactory,
        Nulls $nullsDefault,
        bool $nullsOrderable,
        Direction $direction,
        ?Nulls $nulls,
    ): void {
        $resolver = Mockery::mock(BuilderPropertyResolver::class);
        $builder  = $builderFactory($this);
        $column   = is_string($columnFactory) ? $columnFactory : $columnFactory($this);
        $sorter   = new class($nullsDefault, $nullsOrderable, $resolver) extends DatabaseSorter {
            public function __construct(
                private readonly Nulls $nullsDefault,
                private readonly bool $nullsOrderable,
                BuilderPropertyResolver $resolver,
            ) {
                parent::__construct($resolver);
            }

            #[Override]
            public function sort(
                object $builder,
                Property $property,
                Direction $direction,
                Nulls $nulls = null,
            ): object {
                throw new Exception('Should not be called.');
            }

            #[Override]
            protected function getNullsDefault(EloquentBuilder|QueryBuilder $builder, Direction $direction): Nulls {
                return $this->nullsDefault;
            }

            #[Override]
            protected function isNullsOrderable(EloquentBuilder|QueryBuilder $builder): bool {
                return $this->nullsOrderable;
            }

            #[Override]
            public function sortByColumn(
                EloquentBuilder|QueryBuilder $builder,
                EloquentBuilder|string|QueryBuilder $column,
                Direction $direction,
                Nulls $nulls = null,
            ): EloquentBuilder|QueryBuilder {
                return parent::sortByColumn($builder, $column, $direction, $nulls);
            }
        };

        self::assertDatabaseQueryEquals($expected, $sorter->sortByColumn($builder, $column, $direction, $nulls));
    }

    public function testGetAlias(): void {
        $resolver = Mockery::mock(BuilderPropertyResolver::class);
        $builder  = User::query()->where('name', '=', 'name');
        $sorter   = new class($resolver) extends DatabaseSorter {
            #[Override]
            public function sort(
                object $builder,
                Property $property,
                Direction $direction,
                Nulls $nulls = null,
            ): object {
                throw new Exception('Should not be called.');
            }

            #[Override]
            public function getAlias(EloquentBuilder|QueryBuilder $builder): string {
                return parent::getAlias($builder);
            }
        };

        self::assertEquals('lara_asp_graphql__sort_by__0', $sorter->getAlias($builder));
        self::assertEquals('lara_asp_graphql__sort_by__0', $sorter->getAlias($builder->toBase()));

        $builder->orderBy('name');

        self::assertEquals('lara_asp_graphql__sort_by__1', $sorter->getAlias($builder));
        self::assertEquals('lara_asp_graphql__sort_by__1', $sorter->getAlias($builder->toBase()));

        $builder->union($builder);

        self::assertEquals('lara_asp_graphql__sort_by__1', $sorter->getAlias($builder));
        self::assertEquals('lara_asp_graphql__sort_by__1', $sorter->getAlias($builder->toBase()));

        $builder->orderBy('id');

        self::assertEquals('lara_asp_graphql__sort_by__2', $sorter->getAlias($builder));
        self::assertEquals('lara_asp_graphql__sort_by__2', $sorter->getAlias($builder->toBase()));
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array<array-key, mixed>>
     */
    public static function dataProviderSortByColumn(): array {
        $eloquentBuilderFactory = static function (): EloquentBuilder {
            return User::query()->where('name', '=', 'name');
        };
        $queryBuilderFactory    = static function () use ($eloquentBuilderFactory): QueryBuilder {
            return $eloquentBuilderFactory()->toBase();
        };
        $columnFactory          = static function (): EloquentBuilder {
            return Car::query()->where('user_id', '=', 123);
        };

        return (new CompositeDataProvider(
            new ArrayDataProvider([
                'Eloquent' => [
                    new UnknownValue(),
                    $eloquentBuilderFactory,
                ],
                'Query'    => [
                    new UnknownValue(),
                    $queryBuilderFactory,
                ],
            ]),
            new ArrayDataProvider([
                'null'                                   => [
                    [
                        'query'    => <<<'SQL'
                        select
                            *
                        from
                            "users"
                        where
                            "name" = ?
                        order by
                            "column" asc
                        SQL
                        ,
                        'bindings' => ['name'],
                    ],
                    'column',
                    Nulls::Last,
                    false,
                    Direction::Asc,
                    null,
                ],
                'same'                                   => [
                    [
                        'query'    => <<<'SQL'
                        select
                            *
                        from
                            "users"
                        where
                            "name" = ?
                        order by
                            "column" asc
                        SQL
                        ,
                        'bindings' => ['name'],
                    ],
                    'column',
                    Nulls::Last,
                    false,
                    Direction::Asc,
                    Nulls::Last,
                ],
                'column/non-orderable/nulls first'       => [
                    [
                        'query'    => <<<'SQL'
                        select
                            *
                        from
                            "users"
                        where
                            "name" = ?
                        order by
                            CASE WHEN "table"."column" IS NOT NULL THEN 1 ELSE 0 END,
                            "table"."column" asc
                        SQL
                        ,
                        'bindings' => ['name'],
                    ],
                    'table.column',
                    Nulls::Last,
                    false,
                    Direction::Asc,
                    Nulls::First,
                ],
                'column/non-orderable/nulls last'        => [
                    [
                        'query'    => <<<'SQL'
                        select
                            *
                        from
                            "users"
                        where
                            "name" = ?
                        order by
                            CASE WHEN "table"."column" IS NULL THEN 1 ELSE 0 END,
                            "table"."column" desc
                        SQL
                        ,
                        'bindings' => ['name'],
                    ],
                    'table.column',
                    Nulls::First,
                    false,
                    Direction::Desc,
                    Nulls::Last,
                ],
                'column/orderable/nulls first'           => [
                    [
                        'query'    => <<<'SQL'
                        select
                            *
                        from
                            "users"
                        where
                            "name" = ?
                        order by
                            "table"."column" ASC NULLS FIRST
                        SQL
                        ,
                        'bindings' => ['name'],
                    ],
                    'table.column',
                    Nulls::Last,
                    true,
                    Direction::Asc,
                    Nulls::First,
                ],
                'column/orderable/nulls last'            => [
                    [
                        'query'    => <<<'SQL'
                        select
                            *
                        from
                            "users"
                        where
                            "name" = ?
                        order by
                            "table"."column" DESC NULLS LAST
                        SQL
                        ,
                        'bindings' => ['name'],
                    ],
                    'table.column',
                    Nulls::First,
                    true,
                    Direction::Desc,
                    Nulls::Last,
                ],
                'column-query/non-orderable/nulls first' => [
                    [
                        'query'    => <<<'SQL'
                        select
                            *,
                            (
                                select
                                    *
                                from
                                    "cars"
                                where
                                    "user_id" = ?
                            ) as "lara_asp_graphql__sort_by__0"
                        from
                            "users"
                        where
                            "name" = ?
                        order by
                            CASE WHEN "lara_asp_graphql__sort_by__0" IS NOT NULL THEN 1 ELSE 0 END,
                            "lara_asp_graphql__sort_by__0" asc
                        SQL
                        ,
                        'bindings' => [123, 'name'],
                    ],
                    $columnFactory,
                    Nulls::Last,
                    false,
                    Direction::Asc,
                    Nulls::First,
                ],
                'column-query/non-orderable/nulls last'  => [
                    [
                        'query'    => <<<'SQL'
                        select
                            *,
                            (
                                select
                                    *
                                from
                                    "cars"
                                where
                                    "user_id" = ?
                            ) as "lara_asp_graphql__sort_by__0"
                        from
                            "users"
                        where
                            "name" = ?
                        order by
                            CASE WHEN "lara_asp_graphql__sort_by__0" IS NULL THEN 1 ELSE 0 END,
                            "lara_asp_graphql__sort_by__0" desc
                        SQL
                        ,
                        'bindings' => [123, 'name'],
                    ],
                    $columnFactory,
                    Nulls::First,
                    false,
                    Direction::Desc,
                    Nulls::Last,
                ],
                'column-query/orderable/nulls first'     => [
                    [
                        'query'    => <<<'SQL'
                        select
                            *
                        from
                            "users"
                        where
                            "name" = ?
                        order by
                            (
                                select
                                    *
                                from
                                    "cars"
                                where
                                    "user_id" = ?
                            ) ASC NULLS FIRST
                        SQL
                        ,
                        'bindings' => ['name', 123],
                    ],
                    $columnFactory,
                    Nulls::Last,
                    true,
                    Direction::Asc,
                    Nulls::First,
                ],
                'column-query/orderable/nulls last'      => [
                    [
                        'query'    => <<<'SQL'
                        select
                            *
                        from
                            "users"
                        where
                            "name" = ?
                        order by
                            (
                                select
                                    *
                                from
                                    "cars"
                                where
                                    "user_id" = ?
                            ) DESC NULLS LAST
                        SQL
                        ,
                        'bindings' => ['name', 123],
                    ],
                    $columnFactory,
                    Nulls::First,
                    true,
                    Direction::Desc,
                    Nulls::Last,
                ],
            ]),
        ))->getData();
    }
    // </editor-fold>
}
