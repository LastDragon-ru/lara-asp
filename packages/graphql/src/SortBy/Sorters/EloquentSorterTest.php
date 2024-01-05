<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Sorters;

use Closure;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use LastDragon_ru\LaraASP\Eloquent\Exceptions\PropertyIsNotRelation;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\RelationUnsupported;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\EloquentBuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\Car;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\CarEngine;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\Relations\Unsupported;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\User;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\MergeDataProvider;
use PHPUnit\Framework\Attributes\CoversClass;

use function is_array;

/**
 * @internal
 */
#[CoversClass(EloquentSorter::class)]
class EloquentSorterTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderSort
     *
     * @param array{query: string, bindings: array<array-key, mixed>}|Exception $expected
     * @param Closure(static): EloquentBuilder<EloquentModel>                   $builder
     */
    public function testSort(
        array|Exception $expected,
        Closure $builder,
        Property $property,
        Direction $direction,
    ): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $builder = $builder($this);
        $builder = Container::getInstance()->make(EloquentSorter::class)->sort($builder, $property, $direction);

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
     * @return array<array-key, mixed>
     */
    public static function dataProviderSort(): array {
        return (new MergeDataProvider([
            'Both'     => (new CompositeDataProvider(
                new EloquentBuilderDataProvider(),
                new ArrayDataProvider([
                    'simple condition' => [
                        [
                            'query'    => 'select * from "test_objects" order by "name" desc',
                            'bindings' => [],
                        ],
                        new Property('name'),
                        Direction::Desc,
                    ],
                ]),
            )),
            'Eloquent' => (new ArrayDataProvider([
                'not a relation'      => [
                    new PropertyIsNotRelation(new User(), 'unknown'),
                    static function (): EloquentBuilder {
                        return User::query();
                    },
                    new Property('unknown', 'name'),
                    Direction::Asc,
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
                            MorphMany::class,
                            MorphToMany::class,
                            HasOneThrough::class,
                            HasManyThrough::class,
                        ],
                    ),
                    static function (): EloquentBuilder {
                        return User::query();
                    },
                    new Property('unsupported', 'id'),
                    Direction::Asc,
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
                                ) desc
                            SQL
                        ,
                        'bindings' => [
                            // empty
                        ],
                    ],
                    static function (): EloquentBuilder {
                        return Car::query();
                    },
                    new Property('user', 'organization', 'name'),
                    Direction::Desc,
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
                                ) asc
                            SQL
                        ,
                        'bindings' => [
                            1,
                            1,
                        ],
                    ],
                    static function (): EloquentBuilder {
                        return User::query();
                    },
                    new Property('car', 'engine', 'id'),
                    Direction::Asc,
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
                                ) asc
                            SQL
                        ,
                        'bindings' => [
                            // empty
                        ],
                    ],
                    static function (): EloquentBuilder {
                        return User::query();
                    },
                    new Property('cars', 'name'),
                    Direction::Asc,
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
                                ) asc
                            SQL
                        ,
                        'bindings' => [
                            User::class,
                        ],
                    ],
                    static function (): EloquentBuilder {
                        return User::query();
                    },
                    new Property('avatar', 'id'),
                    Direction::Asc,
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
                                ) desc
                            SQL
                        ,
                        'bindings' => [
                            // empty
                        ],
                    ],
                    static function (): EloquentBuilder {
                        return User::query();
                    },
                    new Property('role', 'user', 'name'),
                    Direction::Desc,
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
                                ) desc
                            SQL
                        ,
                        'bindings' => [
                            // empty
                        ],
                    ],
                    static function (): EloquentBuilder {
                        return User::query();
                    },
                    new Property('roles', 'users', 'name'),
                    Direction::Desc,
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
                                ) asc
                            SQL
                        ,
                        'bindings' => [
                            User::class,
                        ],
                    ],
                    static function (): EloquentBuilder {
                        return User::query();
                    },
                    new Property('tags', 'users', 'name'),
                    Direction::Asc,
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
                                ) asc
                            SQL
                        ,
                        'bindings' => [
                            // empty
                        ],
                    ],
                    static function (): EloquentBuilder {
                        return CarEngine::query();
                    },
                    new Property('users', 'name'),
                    Direction::Asc,
                ],
                MorphMany::class      => [
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
                                ) asc
                            SQL
                        ,
                        'bindings' => [
                            User::class,
                        ],
                    ],
                    static function (): EloquentBuilder {
                        return User::query();
                    },
                    new Property('images', 'id'),
                    Direction::Asc,
                ],
            ])),
        ]))->getData();
    }
    // </editor-fold>
}
