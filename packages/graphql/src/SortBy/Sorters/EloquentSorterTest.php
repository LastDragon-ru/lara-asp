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
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\BuilderPropertyResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Direction;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Nulls;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\RelationUnsupported;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\EloquentBuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\Car;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\CarEngine;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\Relations\Unsupported;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\Role;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\User;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\MergeDataProvider;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;

use function implode;
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
     * @param Closure(object, Property): string|null                            $resolver
     */
    public function testSort(
        array|Exception $expected,
        Closure $builder,
        Property $property,
        Direction $direction,
        ?Nulls $nulls,
        ?Closure $resolver,
    ): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        if ($resolver) {
            $this->override(
                BuilderPropertyResolver::class,
                static function (MockInterface $mock) use ($resolver): void {
                    $mock
                        ->shouldReceive('getProperty')
                        ->once()
                        ->andReturnUsing($resolver);
                },
            );
        }

        $sorter  = Container::getInstance()->make(EloquentSorter::class);
        $builder = $builder($this);
        $builder = $sorter->sort($builder, $property, $direction, $nulls);

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
                        null,
                        null,
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
                    null,
                    null,
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
                    null,
                    null,
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
                                        "lara_asp_graphql__sort_by__0__relation_0"."name"
                                    from
                                        "users"
                                        inner join (
                                            select
                                                *
                                            from
                                                "organizations"
                                        ) as "lara_asp_graphql__sort_by__0__relation_0"
                                            on "lara_asp_graphql__sort_by__0__relation_0"."ownerKey"
                                                = "users"."foreignKey"
                                    where
                                        "cars"."foreignKey" = "users"."ownerKey"
                                        and "deleted_at" is null
                                    order by
                                        "lara_asp_graphql__sort_by__0__relation_0"."name" desc
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
                    null,
                    null,
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
                                        "lara_asp_graphql__sort_by__0__relation_0"."id"
                                    from
                                        "cars"
                                        inner join (
                                            select
                                                *
                                            from
                                                "car_engines"
                                            where
                                                "installed" = ?
                                        ) as "lara_asp_graphql__sort_by__0__relation_0"
                                            on "lara_asp_graphql__sort_by__0__relation_0"."foreignKey"
                                                = "cars"."localKey"
                                    where
                                        "users"."localKey" = "cars"."foreignKey"
                                        and "favorite" = ?
                                    order by
                                        "lara_asp_graphql__sort_by__0__relation_0"."id" asc
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
                    null,
                    null,
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
                    null,
                    null,
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
                    null,
                    null,
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
                                        "lara_asp_graphql__sort_by__0__relation_0"."name"
                                    from
                                        "roles"
                                        inner join "user_roles" on "user_roles"."secondLocalKey" = "roles"."secondKey"
                                        inner join (
                                            select
                                                "user_roles"."id" as "lara_asp_graphql__sort_by__0__relation_0_key",
                                                "users".*
                                            from
                                                "users"
                                                inner join "user_roles"
                                                    on "user_roles"."secondLocalKey" = "users"."secondKey"
                                        ) as "lara_asp_graphql__sort_by__0__relation_0"
                                            on "lara_asp_graphql__sort_by__0__relation_0"
                                                ."lara_asp_graphql__sort_by__0__relation_0_key" = "roles"."localKey"
                                    where
                                        "users"."localKey" = "user_roles"."firstKey"
                                        and "deleted_at" is null
                                    order by
                                        "lara_asp_graphql__sort_by__0__relation_0"."name" desc
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
                    null,
                    null,
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
                                        "lara_asp_graphql__sort_by__0__relation_0"."name"
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
                                        ) as "lara_asp_graphql__sort_by__0__relation_0"
                                            on "lara_asp_graphql__sort_by__0__relation_0"
                                                ."parentKey" = "roles"."relatedKey"
                                    where
                                        "users"."parentKey" = "user_roles"."foreignPivotKey"
                                        and "deleted_at" is null
                                    order by
                                        "lara_asp_graphql__sort_by__0__relation_0"."name" desc
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
                    null,
                    null,
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
                                        "lara_asp_graphql__sort_by__0__relation_0"."name"
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
                                        ) as "lara_asp_graphql__sort_by__0__relation_0"
                                            on "lara_asp_graphql__sort_by__0__relation_0"
                                                ."parentKey" = "tags"."relatedKey"
                                    where
                                        "users"."parentKey" = "taggables"."foreignPivotKey"
                                        and "taggables"."taggable_type" = ?
                                    order by
                                        "lara_asp_graphql__sort_by__0__relation_0"."name" asc
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
                    null,
                    null,
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
                    null,
                    null,
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
                    null,
                    null,
                ],
                'nulls'               => [
                    [
                        'query'    => <<<'SQL'
                            select
                                *
                            from
                                "users"
                            order by
                                "name" DESC NULLS FIRST
                            SQL
                        ,
                        'bindings' => [
                            // empty
                        ],
                    ],
                    static function (): EloquentBuilder {
                        return User::query();
                    },
                    new Property('name'),
                    Direction::Desc,
                    Nulls::First,
                    null,
                ],
                'resolver'            => [
                    [
                        'query'    => <<<'SQL'
                            select
                                *
                            from
                                "users"
                            order by
                                "resolved__name" asc
                            SQL
                        ,
                        'bindings' => [
                            // empty
                        ],
                    ],
                    static function (): EloquentBuilder {
                        return User::query();
                    },
                    new Property('name'),
                    Direction::Asc,
                    null,
                    static function (object $builder, Property $property): string {
                        self::assertInstanceOf(EloquentBuilder::class, $builder);
                        self::assertInstanceOf(User::class, $builder->getModel());

                        return 'resolved__'.implode('__', $property->getPath());
                    },
                ],
                'resolver (relation)' => [
                    [
                        'query'    => <<<'SQL'
                            select
                                *
                            from
                                "users"
                            order by
                                (
                                    select
                                        "lara_asp_graphql__sort_by__0__relation_1"."resolved__name"
                                    from
                                        "cars"
                                        inner join (
                                            select
                                                *
                                            from
                                                "users"
                                            where
                                                "deleted_at" is null
                                        ) as "lara_asp_graphql__sort_by__0__relation_0"
                                            on "lara_asp_graphql__sort_by__0__relation_0"."ownerKey"
                                                = "cars"."foreignKey"
                                        inner join (
                                            select
                                                *
                                            from
                                                "roles"
                                                inner join "user_roles"
                                                    on "roles"."relatedKey" = "user_roles"."relatedPivotKey"
                                            where
                                                "deleted_at" is null
                                        ) as "lara_asp_graphql__sort_by__0__relation_1"
                                            on "lara_asp_graphql__sort_by__0__relation_1"."parentKey"
                                                = "lara_asp_graphql__sort_by__0__relation_0"."relatedKey"
                                    where
                                        "users"."localKey" = "cars"."foreignKey"
                                        and "deleted_at" is null
                                    order by
                                        "lara_asp_graphql__sort_by__0__relation_1"."resolved__name" asc
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
                    new Property('cars', 'user', 'roles', 'name'),
                    Direction::Asc,
                    null,
                    static function (object $builder, Property $property): string {
                        self::assertInstanceOf(EloquentBuilder::class, $builder);
                        self::assertInstanceOf(Role::class, $builder->getModel());

                        return implode('.', $property->getParent()->getPath()).'.resolved__'.$property->getName();
                    },
                ],
            ])),
        ]))->getData();
    }
    // </editor-fold>
}
