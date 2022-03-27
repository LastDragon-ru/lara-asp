<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Eloquent;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use LastDragon_ru\LaraASP\Eloquent\Exceptions\PropertyIsNotRelation;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Clause;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\RelationUnsupported;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\EloquentBuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\Car;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\CarEngine;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\Relations\Unsupported;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\User;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\MergeDataProvider;

use function is_array;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Eloquent\Builder
 *
 * @phpstan-import-type BuilderFactory from \LastDragon_ru\LaraASP\GraphQL\Testing\Package\BuilderDataProvider
 */
class BuilderTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::handle
     *
     * @dataProvider dataProviderHandle
     *
     * @param array{query: string, bindings: array<mixed>}|Exception $expected
     * @param BuilderFactory                                         $builder
     * @param array<Clause>                                          $clauses
     */
    public function testHandle(array|Exception $expected, Closure $builder, array $clauses): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $builder = $builder($this);
        $builder = $this->app->make(Builder::class)->handle($builder, $clauses);

        if (is_array($expected)) {
            self::assertDatabaseQueryEquals($expected, $builder);
        } else {
            self::fail('Something wrong...');
        }
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    public function dataProviderHandle(): array {
        return (new MergeDataProvider([
            'Both'     => (new CompositeDataProvider(
                new EloquentBuilderDataProvider(),
                new ArrayDataProvider([
                    'empty' => [
                        [
                            'query'    => 'select * from "tmp"',
                            'bindings' => [],
                        ],
                        [],
                    ],
                ]),
            )),
            'Eloquent' => (new ArrayDataProvider([
                'not a relation'      => [
                    new PropertyIsNotRelation(new User(), 'unknown'),
                    static function (): EloquentBuilder {
                        return User::query();
                    },
                    [
                        new Clause(['unknown', 'name'], 'asc'),
                    ],
                ],
                'unsupported'         => [
                    new RelationUnsupported(
                        'unsupported',
                        Unsupported::class,
                        [
                            BelongsTo::class,
                            BelongsToMany::class,
                            HasOne::class,
                            HasMany::class,
                            MorphOne::class,
                            MorphToMany::class,
                            HasOneThrough::class,
                            HasManyThrough::class,
                        ],
                    ),
                    static function (): EloquentBuilder {
                        return User::query();
                    },
                    [
                        new Clause(['unsupported', 'id'], 'asc'),
                    ],
                ],
                'simple condition'    => [
                    [
                        'query'    => 'select * from "users" order by "name" desc, "id" asc',
                        'bindings' => [],
                    ],
                    static function (): EloquentBuilder {
                        return User::query();
                    },
                    [
                        new Clause(['name'], 'desc'),
                        new Clause(['id'], 'asc'),
                    ],
                ],
                BelongsTo::class      => [
                    [
                        'query'    => <<<'SQL'
                            select
                                *
                            from
                                "cars"
                            order by
                                (
                                    select
                                        "users"."name"
                                    from
                                        "users"
                                    where
                                        "cars"."foreignKey" = "users"."ownerKey"
                                        and "deleted_at" is null
                                    order by
                                        "users"."name" asc
                                    limit
                                        1
                                ) asc,
                                (
                                    select
                                        "sort_by_organization"."name"
                                    from
                                        "users"
                                        inner join (
                                            select
                                                *
                                            from
                                                "organizations"
                                        ) as "sort_by_organization"
                                            on "sort_by_organization"."ownerKey" = "users"."foreignKey"
                                    where
                                        "cars"."foreignKey" = "users"."ownerKey"
                                        and "deleted_at" is null
                                    order by
                                        "sort_by_organization"."name" desc
                                    limit
                                        1
                                ) desc,
                                "name" asc
                            SQL
                        ,
                        'bindings' => [
                            // empty
                        ],
                    ],
                    static function (): EloquentBuilder {
                        return Car::query();
                    },
                    [
                        new Clause(['user', 'name'], 'asc'),
                        new Clause(['user', 'organization', 'name'], 'desc'),
                        new Clause(['name'], 'asc'),
                    ],
                ],
                HasOne::class         => [
                    [
                        'query'    => <<<'SQL'
                            select
                                *
                            from
                                "users"
                            order by
                                (
                                    select
                                        "cars"."name"
                                    from
                                        "cars"
                                    where
                                        "users"."localKey" = "cars"."foreignKey"
                                        and "favorite" = ?
                                    order by
                                        "cars"."name" desc
                                    limit
                                        1
                                ) desc,
                                (
                                    select
                                        "sort_by_engine"."id"
                                    from
                                        "cars"
                                        inner join (
                                            select
                                                *
                                            from
                                                "car_engines"
                                            where
                                                "installed" = ?
                                        ) as "sort_by_engine" on "sort_by_engine"."foreignKey" = "cars"."localKey"
                                    where
                                        "users"."localKey" = "cars"."foreignKey"
                                        and "favorite" = ?
                                    order by
                                        "sort_by_engine"."id" asc
                                    limit
                                        1
                                ) asc,
                                "name" asc
                            SQL
                        ,
                        'bindings' => [
                            1,
                            1,
                            1,
                        ],
                    ],
                    static function (): EloquentBuilder {
                        return User::query();
                    },
                    [
                        new Clause(['car', 'name'], 'desc'),
                        new Clause(['car', 'engine', 'id'], 'asc'),
                        new Clause(['name'], 'asc'),
                    ],
                ],
                HasMany::class        => [
                    [
                        'query'    => <<<'SQL'
                            select
                                *
                            from
                                "users"
                            order by
                                (
                                    select
                                        "cars"."name"
                                    from
                                        "cars"
                                    where
                                        "users"."localKey" = "cars"."foreignKey"
                                        and "deleted_at" is null
                                    order by
                                        "cars"."name" asc
                                    limit
                                        1
                                ) asc,
                                (
                                    select
                                        "cars"."engines"
                                    from
                                        "cars"
                                    where
                                        "users"."localKey" = "cars"."foreignKey"
                                        and "deleted_at" is null
                                    order by
                                        "cars"."engines" desc
                                    limit
                                        1
                                ) desc,
                                "name" asc
                            SQL
                        ,
                        'bindings' => [
                            // empty
                        ],
                    ],
                    static function (): EloquentBuilder {
                        return User::query();
                    },
                    [
                        new Clause(['cars', 'name'], 'asc'),
                        new Clause(['cars', 'engines'], 'desc'),
                        new Clause(['name'], 'asc'),
                    ],
                ],
                MorphOne::class       => [
                    [
                        'query'    => <<<'SQL'
                            select
                                *
                            from
                                "users"
                            order by
                                (
                                    select
                                        "images"."id"
                                    from
                                        "images"
                                    where
                                        "users"."localKey" = "images"."imageable_id"
                                        and "images"."imageable_type" = ?
                                        and "deleted_at" is null
                                    order by
                                        "images"."id" asc
                                    limit
                                        1
                                ) asc,
                                "name" asc
                            SQL
                        ,
                        'bindings' => [
                            User::class,
                        ],
                    ],
                    static function (): EloquentBuilder {
                        return User::query();
                    },
                    [
                        new Clause(['avatar', 'id'], 'asc'),
                        new Clause(['name'], 'asc'),
                    ],
                ],
                HasOneThrough::class  => [
                    [
                        'query'    => <<<'SQL'
                            select
                                *
                            from
                                "users"
                            order by
                                (
                                    select
                                        "roles"."name"
                                    from
                                        "roles"
                                        inner join "user_roles" on "user_roles"."secondLocalKey" = "roles"."secondKey"
                                    where
                                        "users"."localKey" = "user_roles"."firstKey"
                                        and "deleted_at" is null
                                    order by
                                        "roles"."name" asc
                                    limit
                                        1
                                ) asc,
                                (
                                    select
                                        "sort_by_user"."name"
                                    from
                                        "roles"
                                        inner join "user_roles" on "user_roles"."secondLocalKey" = "roles"."secondKey"
                                        inner join (
                                            select
                                                "user_roles"."id" as "sort_by_user_key",
                                                "users".*
                                            from
                                                "users"
                                                inner join "user_roles"
                                                    on "user_roles"."secondLocalKey" = "users"."secondKey"
                                        ) as "sort_by_user" on "sort_by_user"."sort_by_user_key" = "roles"."localKey"
                                    where
                                        "users"."localKey" = "user_roles"."firstKey"
                                        and "deleted_at" is null
                                    order by
                                        "sort_by_user"."name" desc
                                    limit
                                        1
                                ) desc,
                                "name" desc
                            SQL
                        ,
                        'bindings' => [
                            // empty
                        ],
                    ],
                    static function (): EloquentBuilder {
                        return User::query();
                    },
                    [
                        new Clause(['role', 'name'], 'asc'),
                        new Clause(['role', 'user', 'name'], 'desc'),
                        new Clause(['name'], 'desc'),
                    ],
                ],
                BelongsToMany::class  => [
                    [
                        'query'    => <<<'SQL'
                            select
                                *
                            from
                                "users"
                            order by
                                (
                                    select
                                        "roles"."name"
                                    from
                                        "roles"
                                        inner join "user_roles" on "roles"."relatedKey" = "user_roles"."relatedPivotKey"
                                    where
                                        "users"."parentKey" = "user_roles"."foreignPivotKey"
                                        and "deleted_at" is null
                                    order by
                                        "roles"."name" asc
                                    limit
                                        1
                                ) asc,
                                (
                                    select
                                        "sort_by_users"."name"
                                    from
                                        "roles"
                                        inner join "user_roles" on "roles"."relatedKey" = "user_roles"."relatedPivotKey"
                                        inner join (
                                            select
                                                *
                                            from
                                                "users"
                                                inner join "user_roles"
                                                    on "users"."relatedKey" = "user_roles"."relatedPivotKey"
                                            where
                                                "deleted_at" is null
                                        ) as "sort_by_users" on "sort_by_users"."parentKey" = "roles"."relatedKey"
                                    where
                                        "users"."parentKey" = "user_roles"."foreignPivotKey"
                                        and "deleted_at" is null
                                    order by
                                        "sort_by_users"."name" desc
                                    limit
                                        1
                                ) desc,
                                "name" desc
                            SQL
                        ,
                        'bindings' => [
                            // empty
                        ],
                    ],
                    static function (): EloquentBuilder {
                        return User::query();
                    },
                    [
                        new Clause(['roles', 'name'], 'asc'),
                        new Clause(['roles', 'users', 'name'], 'desc'),
                        new Clause(['name'], 'desc'),
                    ],
                ],
                MorphToMany::class    => [
                    [
                        'query'    => <<<'SQL'
                            select
                                *
                            from
                                "users"
                            order by
                                (
                                    select
                                        "tags"."id"
                                    from
                                        "tags"
                                        inner join "taggables" on "tags"."relatedKey" = "taggables"."relatedPivotKey"
                                    where
                                        "users"."parentKey" = "taggables"."foreignPivotKey"
                                        and "taggables"."taggable_type" = ?
                                    order by
                                        "tags"."id" asc
                                    limit
                                        1
                                ) asc,
                                (
                                    select
                                        "sort_by_users"."name"
                                    from
                                        "tags"
                                        inner join "taggables" on "tags"."relatedKey" = "taggables"."relatedPivotKey"
                                        inner join (
                                            select
                                                *
                                            from
                                                "users"
                                                inner join "taggables"
                                                    on "users"."relatedKey" = "taggables"."relatedPivotKey"
                                        ) as "sort_by_users" on "sort_by_users"."parentKey" = "tags"."relatedKey"
                                    where
                                        "users"."parentKey" = "taggables"."foreignPivotKey"
                                        and "taggables"."taggable_type" = ?
                                    order by
                                        "sort_by_users"."name" asc
                                    limit
                                        1
                                ) asc,
                                "name" desc
                            SQL
                        ,
                        'bindings' => [
                            User::class,
                            User::class,
                        ],
                    ],
                    static function (): EloquentBuilder {
                        return User::query();
                    },
                    [
                        new Clause(['tags', 'id'], 'asc'),
                        new Clause(['tags', 'users', 'name'], 'asc'),
                        new Clause(['name'], 'desc'),
                    ],
                ],
                HasManyThrough::class => [
                    [
                        'query'    => <<<'SQL'
                            select
                                *
                            from
                                "car_engines"
                            order by
                                (
                                    select
                                        "users"."name"
                                    from
                                        "users"
                                        inner join "cars" on "cars"."secondLocalKey" = "users"."secondKey"
                                    where
                                        "car_engines"."localKey" = "cars"."firstKey"
                                        and "deleted_at" is null
                                    order by
                                        "users"."name" asc
                                    limit
                                        1
                                ) asc,
                                "id" desc
                            SQL
                        ,
                        'bindings' => [
                            // empty
                        ],
                    ],
                    static function (): EloquentBuilder {
                        return CarEngine::query();
                    },
                    [
                        new Clause(['users', 'name'], 'asc'),
                        new Clause(['id'], 'desc'),
                    ],
                ],
            ])),
        ]))->getData();
    }
    // </editor-fold>
}
