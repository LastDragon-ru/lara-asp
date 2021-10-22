<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Eloquent;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use LastDragon_ru\LaraASP\Eloquent\Exceptions\PropertyIsNotRelation;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Clause;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\RelationUnsupported;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\EloquentBuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\MergeDataProvider;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Eloquent\Builder
 */
class BuilderTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::handle
     *
     * @dataProvider dataProviderHandle
     *
     * @param array<mixed>|Exception $expected
     * @param array<Clause>          $clauses
     */
    public function testHandle(array|Exception $expected, Closure $builder, array $clauses): void {
        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $builder = $builder($this);
        $builder = $this->app->make(Builder::class)->handle($builder, $clauses);

        $this->assertDatabaseQueryEquals($expected, $builder);
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
                'not a relation'     => [
                    new PropertyIsNotRelation(new SortBuilderTest__ModelA(), 'delete'),
                    static function (): EloquentBuilder {
                        return SortBuilderTest__ModelA::query();
                    },
                    [
                        new Clause(['delete', 'name'], 'asc'),
                    ],
                ],
                'unsupported'        => [
                    new RelationUnsupported(
                        'unsupported',
                        HasMany::class,
                        [
                            BelongsTo::class,
                            HasOne::class,
                            MorphOne::class,
                            HasOneThrough::class,
                        ],
                    ),
                    static function (): EloquentBuilder {
                        return SortBuilderTest__ModelA::query();
                    },
                    [
                        new Clause(['unsupported', 'name'], 'asc'),
                    ],
                ],
                'simple condition'   => [
                    [
                        'query'    => 'select * from "table_a" order by "a" asc, "b" desc',
                        'bindings' => [],
                    ],
                    static function (): EloquentBuilder {
                        return SortBuilderTest__ModelA::query();
                    },
                    [
                        new Clause(['a'], 'asc'),
                        new Clause(['b'], 'desc'),
                    ],
                ],
                BelongsTo::class     => [
                    [
                        'query'    => <<<'SQL'
                            select
                                *
                            from
                                "table_a"
                            order by
                                (
                                    select
                                        "table_b"."name"
                                    from
                                        "table_b"
                                    where
                                        "table_a"."belongs_to_b_id" = "table_b"."id"
                                        and "a" = ?
                                    limit
                                        1
                                ) asc,
                                (
                                    select
                                        "table_b"."created_at"
                                    from
                                        "table_b"
                                    where
                                        "table_a"."belongs_to_b_id" = "table_b"."id"
                                        and "a" = ?
                                    limit
                                        1
                                ) desc,
                                (
                                    select
                                        "table_b"."name"
                                    from
                                        "table_b"
                                        inner join (
                                            select
                                                *
                                            from
                                                "table_c"
                                        ) as "sort_by_belongsToC"
                                            on "sort_by_belongsToC"."id" = "table_b"."belongs_to_c_id"
                                    where
                                        "table_a"."belongs_to_b_id" = "table_b"."id"
                                        and "a" = ?
                                    limit
                                        1
                                ) desc,
                                (
                                    select
                                        "table_b"."created_at"
                                    from
                                        "table_b"
                                        inner join (
                                            select
                                                *
                                            from
                                                "table_c"
                                        ) as "sort_by_belongsToC"
                                            on "sort_by_belongsToC"."id" = "table_b"."belongs_to_c_id"
                                    where
                                        "table_a"."belongs_to_b_id" = "table_b"."id"
                                        and "a" = ?
                                    limit
                                        1
                                ) desc,
                                "name" asc
                            SQL
                        ,
                        'bindings' => [
                            'a',
                            'a',
                            'a',
                            'a',
                        ],
                    ],
                    static function (): EloquentBuilder {
                        return SortBuilderTest__ModelA::query();
                    },
                    [
                        new Clause(['belongsToB', 'name'], 'asc'),
                        new Clause(['belongsToB', 'created_at'], 'desc'),
                        new Clause(['belongsToB', 'belongsToC', 'name'], 'desc'),
                        new Clause(['belongsToB', 'belongsToC', 'created_at'], 'desc'),
                        new Clause(['name'], 'asc'),
                    ],
                ],
                HasOne::class        => [
                    [
                        'query'    => <<<'SQL'
                            select
                                *
                            from
                                "table_a"
                            order by
                                (
                                    select
                                        "table_b"."name"
                                    from
                                        "table_b"
                                    where
                                        "table_a"."id" = "table_b"."model_a_id"
                                        and "b" = ?
                                    limit
                                        1
                                ) asc,
                                (
                                    select
                                        "table_b"."created_at"
                                    from
                                        "table_b"
                                    where
                                        "table_a"."id" = "table_b"."model_a_id"
                                        and "b" = ?
                                    limit
                                        1
                                ) desc,
                                (
                                    select
                                        "table_b"."name"
                                    from
                                        "table_b"
                                        inner join (
                                            select
                                                *
                                            from
                                                "table_c"
                                        ) as "sort_by_hasOneC" on "sort_by_hasOneC"."model_b_id" = "table_b"."id"
                                    where
                                        "table_a"."id" = "table_b"."model_a_id"
                                        and "b" = ?
                                    limit
                                        1
                                ) desc,
                                (
                                    select
                                        "table_b"."created_at"
                                    from
                                        "table_b"
                                        inner join (
                                            select
                                                *
                                            from
                                                "table_c"
                                        ) as "sort_by_hasOneC" on "sort_by_hasOneC"."model_b_id" = "table_b"."id"
                                    where
                                        "table_a"."id" = "table_b"."model_a_id"
                                        and "b" = ?
                                    limit
                                        1
                                ) desc,
                                "name" asc
                            SQL
                        ,
                        'bindings' => [
                            'b',
                            'b',
                            'b',
                            'b',
                        ],
                    ],
                    static function (): EloquentBuilder {
                        return SortBuilderTest__ModelA::query();
                    },
                    [
                        new Clause(['hasOneB', 'name'], 'asc'),
                        new Clause(['hasOneB', 'created_at'], 'desc'),
                        new Clause(['hasOneB', 'hasOneC', 'name'], 'desc'),
                        new Clause(['hasOneB', 'hasOneC', 'created_at'], 'desc'),
                        new Clause(['name'], 'asc'),
                    ],
                ],
                MorphOne::class      => [
                    [
                        'query'    => <<<'SQL'
                            select
                                *
                            from
                                "table_a"
                            order by
                                (
                                    select
                                        "table_b"."name"
                                    from
                                        "table_b"
                                    where
                                        "table_a"."id" = "table_b"."morphable_a_id"
                                        and "table_b"."morphable_a_type" = ?
                                        and "c" = ?
                                    limit
                                        1
                                ) asc,
                                (
                                    select
                                        "table_b"."created_at"
                                    from
                                        "table_b"
                                    where
                                        "table_a"."id" = "table_b"."morphable_a_id"
                                        and "table_b"."morphable_a_type" = ?
                                        and "c" = ?
                                    limit
                                        1
                                ) desc,
                                (
                                    select
                                        "table_b"."name"
                                    from
                                        "table_b"
                                        inner join (
                                            select
                                                *
                                            from
                                                "table_c"
                                        ) as "sort_by_morphOneC"
                                            on "sort_by_morphOneC"."morphable_b_id" = "table_b"."id"
                                        and "sort_by_morphOneC"."morphable_b_type" = ?
                                    where
                                        "table_a"."id" = "table_b"."morphable_a_id"
                                        and "table_b"."morphable_a_type" = ?
                                        and "c" = ?
                                    limit
                                        1
                                ) desc,
                                (
                                    select
                                        "table_b"."created_at"
                                    from
                                        "table_b"
                                        inner join (
                                            select
                                                *
                                            from
                                                "table_c"
                                        ) as "sort_by_morphOneC"
                                            on "sort_by_morphOneC"."morphable_b_id" = "table_b"."id"
                                        and "sort_by_morphOneC"."morphable_b_type" = ?
                                    where
                                        "table_a"."id" = "table_b"."morphable_a_id"
                                        and "table_b"."morphable_a_type" = ?
                                        and "c" = ?
                                    limit
                                        1
                                ) desc,
                                "name" asc
                            SQL
                        ,
                        'bindings' => [
                            SortBuilderTest__ModelA::class,
                            'c',
                            SortBuilderTest__ModelA::class,
                            'c',
                            SortBuilderTest__ModelB::class,
                            SortBuilderTest__ModelA::class,
                            'c',
                            SortBuilderTest__ModelB::class,
                            SortBuilderTest__ModelA::class,
                            'c',
                        ],
                    ],
                    static function (): EloquentBuilder {
                        return SortBuilderTest__ModelA::query();
                    },
                    [
                        new Clause(['morphOneB', 'name'], 'asc'),
                        new Clause(['morphOneB', 'created_at'], 'desc'),
                        new Clause(['morphOneB', 'morphOneC', 'name'], 'desc'),
                        new Clause(['morphOneB', 'morphOneC', 'created_at'], 'desc'),
                        new Clause(['name'], 'asc'),
                    ],
                ],
                HasOneThrough::class => [
                    [
                        'query'    => <<<'SQL'
                            select
                                *
                            from
                                "table_a"
                            order by
                                (
                                    select
                                        "table_c"."name"
                                    from
                                        "table_c"
                                        inner join "table_b" on "table_b"."second_local_key" = "table_c"."second_key"
                                    where
                                        "table_a"."local_key" = "table_b"."first_key"
                                    limit
                                        1
                                ) asc,
                                (
                                    select
                                        "table_c"."created_at"
                                    from
                                        "table_c"
                                        inner join "table_b" on "table_b"."second_local_key" = "table_c"."second_key"
                                    where
                                        "table_a"."local_key" = "table_b"."first_key"
                                    limit
                                        1
                                ) desc,
                                (
                                    select
                                        "table_c"."name"
                                    from
                                        "table_c"
                                        inner join "table_b" on "table_b"."second_local_key" = "table_c"."second_key"
                                        inner join (
                                            select
                                                "table_b"."id" as "sort_by_hasOneThroughA_key",
                                                "table_a".*
                                            from
                                                "table_a"
                                                inner join "table_b"
                                                    on "table_b"."second_local_key" = "table_a"."second_key"
                                        ) as "sort_by_hasOneThroughA"
                                            on "sort_by_hasOneThroughA"."sort_by_hasOneThroughA_key"
                                                   = "table_c"."local_key"
                                    where
                                        "table_a"."local_key" = "table_b"."first_key"
                                    limit
                                        1
                                ) desc,
                                (
                                    select
                                        "table_c"."created_at"
                                    from
                                        "table_c"
                                        inner join "table_b" on "table_b"."second_local_key" = "table_c"."second_key"
                                        inner join (
                                            select
                                                "table_b"."id" as "sort_by_hasOneThroughA_key",
                                                "table_a".*
                                            from
                                                "table_a"
                                                inner join "table_b"
                                                    on "table_b"."second_local_key" = "table_a"."second_key"
                                        ) as "sort_by_hasOneThroughA"
                                            on "sort_by_hasOneThroughA"."sort_by_hasOneThroughA_key"
                                                   = "table_c"."local_key"
                                    where
                                        "table_a"."local_key" = "table_b"."first_key"
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
                        return SortBuilderTest__ModelA::query();
                    },
                    [
                        new Clause(['hasOneThroughC', 'name'], 'asc'),
                        new Clause(['hasOneThroughC', 'created_at'], 'desc'),
                        new Clause(['hasOneThroughC', 'hasOneThroughA', 'name'], 'desc'),
                        new Clause(['hasOneThroughC', 'hasOneThroughA', 'created_at'], 'desc'),
                        new Clause(['name'], 'asc'),
                    ],
                ],
            ])),
        ]))->getData();
    }
    // </editor-fold>
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class SortBuilderTest__ModelA extends Model {
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     *
     * @var string
     */
    public $table = 'table_a';

    public function __construct() {
        self::unguard(true);
        parent::__construct([
            $this->getKeyName() => 12,
            'belongs_to_b_id'   => 34,
        ]);
    }

    public function belongsToB(): BelongsTo {
        return $this
            ->belongsTo(SortBuilderTest__ModelB::class)
            ->where('a', '=', 'a');
    }

    public function hasOneB(): HasOne {
        return $this
            ->hasOne(SortBuilderTest__ModelB::class, 'model_a_id')
            ->where('b', '=', 'b');
    }

    public function morphOneB(): MorphOne {
        return $this
            ->morphOne(SortBuilderTest__ModelB::class, 'morphable_a')
            ->where('c', '=', 'c');
    }

    public function hasOneThroughC(): HasOneThrough {
        return $this->hasOneThrough(
            SortBuilderTest__ModelC::class,
            SortBuilderTest__ModelB::class,
            'first_key',
            'second_key',
            'local_key',
            'second_local_key',
        );
    }

    public function unsupported(): HasMany {
        return $this->hasMany(SortBuilderTest__ModelB::class);
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class SortBuilderTest__ModelB extends Model {
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     *
     * @var string
     */
    public $table = 'table_b';

    public function __construct() {
        self::unguard(true);
        parent::__construct([
            $this->getKeyName() => 56,
            'belongs_to_c_id'   => 78,
        ]);
    }

    public function belongsToC(): BelongsTo {
        return $this->belongsTo(SortBuilderTest__ModelC::class);
    }

    public function hasOneC(): HasOne {
        return $this->hasOne(SortBuilderTest__ModelC::class, 'model_b_id');
    }

    public function morphOneC(): MorphOne {
        return $this->morphOne(SortBuilderTest__ModelC::class, 'morphable_b');
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class SortBuilderTest__ModelC extends Model {
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     *
     * @var string
     */
    public $table = 'table_c';

    public function __construct() {
        self::unguard(true);
        parent::__construct([
            $this->getKeyName() => 90,
        ]);
    }

    public function hasOneThroughA(): HasOneThrough {
        return $this->hasOneThrough(
            SortBuilderTest__ModelA::class,
            SortBuilderTest__ModelB::class,
            'first_key',
            'second_key',
            'local_key',
            'second_local_key',
        );
    }
}
