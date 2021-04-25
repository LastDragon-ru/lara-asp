<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use LogicException;

use function implode;
use function sprintf;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SortBy\SortBuilder
 */
class SortBuilderTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::build
     *
     * @dataProvider dataProviderBuild
     * @dataProvider dataProviderBuildQuery
     * @dataProvider dataProviderBuildEloquent
     *
     * @param array<mixed> $clause
     */
    public function testBuild(array|Exception $expected, Closure $builder, array $clause): void {
        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $directive = $this->app->make(Directive::class);
        $builder   = $builder($this);
        $builder   = $directive->handleBuilder($builder, $clause);
        $actual    = $this->getSql($builder);

        $this->assertEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    public function dataProviderBuild(): array {
        return (new CompositeDataProvider(
            new BuilderDataProvider(),
            new ArrayDataProvider([
                'general: empty'                  => [
                    [
                        'sql'      => 'select * from "tmp"',
                        'bindings' => [],
                    ],
                    [],
                ],
                'general: empty clause'           => [
                    new SortLogicException(
                        'Sort clause cannot be empty.',
                    ),
                    [
                        [],
                    ],
                ],
                'general: more than one property' => [
                    new SortLogicException(
                        'Only one property allowed, found: `a`, `b`.',
                    ),
                    [
                        [
                            'a' => 'asc',
                            'b' => 'asc',
                        ],
                    ],
                ],
            ]),
        ))->getData();
    }

    /**
     * @return array<mixed>
     */
    public function dataProviderBuildQuery(): array {
        return [
            'query: simple condition' => [
                [
                    'sql'      => 'select * from "tmp" order by "a" asc',
                    'bindings' => [],
                ],
                static function (TestCase $test): QueryBuilder {
                    return $test->app->make('db')->table('tmp');
                },
                [
                    [
                        'a' => 'asc',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    public function dataProviderBuildEloquent(): array {
        return [
            'eloquent: query builder not supported' => [
                new SortLogicException(sprintf(
                    'Relation cannot be used with `%s`.',
                    QueryBuilder::class,
                )),
                static function (self $test): QueryBuilder {
                    return $test->app->make('db')->table('tmp');
                },
                [
                    [
                        'test' => ['name' => 'asc'],
                    ],
                ],
            ],
            'eloquent: not a relation'              => [
                new LogicException(sprintf(
                    'Property `%s` is not a relation.',
                    'delete',
                )),
                static function (): EloquentBuilder {
                    return SortBuilderTest__ModelA::query();
                },
                [
                    [
                        'delete' => ['name' => 'asc'],
                    ],
                ],
            ],
            'eloquent: unsupported'                 => [
                new SortLogicException(sprintf(
                    'Relation of type `%s` cannot be used for sort, only `%s` supported.',
                    HasMany::class,
                    implode('`, `', [
                        BelongsTo::class,
                        HasOne::class,
                        MorphOne::class,
                    ]),
                )),
                static function (): EloquentBuilder {
                    return SortBuilderTest__ModelA::query();
                },
                [
                    [
                        'unsupported' => ['name' => 'asc'],
                    ],
                ],
            ],
            'eloquent: simple condition'            => [
                [
                    'sql'      => 'select * from "table_a" order by "table_a"."a" asc, "table_a"."b" desc',
                    'bindings' => [],
                ],
                static function (): EloquentBuilder {
                    return SortBuilderTest__ModelA::query();
                },
                [
                    [
                        'a' => 'asc',
                    ],
                    [
                        'b' => 'desc',
                    ],
                ],
            ],
            'eloquent: '.BelongsTo::class           => [
                [
                    'sql'      => ''.
                        'select'.
                        ' "table_a".*,'.
                        ' "table_alias_0"."name" as "table_alias_0_name",'.
                        ' "table_alias_0"."created_at" as "table_alias_0_created_at",'.
                        ' "table_alias_1"."name" as "table_alias_1_name",'.
                        ' "table_alias_1"."created_at" as "table_alias_1_created_at" '.
                        'from "table_a" '.
                        'left join (select * from "table_b" where "table_b"."id" = ? and "a" = ?)'.
                        ' as "table_alias_0" on "table_alias_0"."id" = "table_a"."belongs_to_b_id" '.
                        'left join (select * from "table_c" where "table_c"."id" = ?)'.
                        ' as "table_alias_1" on "table_alias_1"."id" = "table_alias_0"."belongs_to_c_id" '.
                        'order by'.
                        ' "table_alias_0_name" asc,'.
                        ' "table_alias_0_created_at" desc,'.
                        ' "table_alias_1_name" desc,'.
                        ' "table_alias_1_created_at" desc,'.
                        ' "table_a"."name" asc',
                    'bindings' => [
                        34,
                        'a',
                        78,
                    ],
                ],
                static function (): EloquentBuilder {
                    return SortBuilderTest__ModelA::query();
                },
                [
                    [
                        'belongsToB' => ['name' => 'asc'],
                    ],
                    [
                        'belongsToB' => ['created_at' => 'desc'],
                    ],
                    [
                        'belongsToB' => [
                            'belongsToC' => ['name' => 'desc'],
                        ],
                    ],
                    [
                        'belongsToB' => [
                            'belongsToC' => ['created_at' => 'desc'],
                        ],
                    ],
                    [
                        'name' => 'asc',
                    ],
                ],
            ],
            'eloquent: '.HasOne::class              => [
                [
                    'sql'      => ''.
                        'select'.
                        ' "table_a".*,'.
                        ' "table_alias_0"."name" as "table_alias_0_name",'.
                        ' "table_alias_0"."created_at" as "table_alias_0_created_at",'.
                        ' "table_alias_1"."name" as "table_alias_1_name",'.
                        ' "table_alias_1"."created_at" as "table_alias_1_created_at" '.
                        'from "table_a" '.
                        'left join (select * from "table_b" where'.
                        ' "table_b"."model_a_id" = ? and "table_b"."model_a_id" is not null and "b" = ?'.
                        ') as "table_alias_0" on "table_alias_0"."model_a_id" = "table_a"."id" '.
                        'left join (select * from "table_c" where'.
                        ' "table_c"."model_b_id" = ? and "table_c"."model_b_id" is not null'.
                        ') as "table_alias_1" on "table_alias_1"."model_b_id" = "table_alias_0"."id" '.
                        'order by'.
                        ' "table_alias_0_name" asc,'.
                        ' "table_alias_0_created_at" desc,'.
                        ' "table_alias_1_name" desc,'.
                        ' "table_alias_1_created_at" desc,'.
                        ' "table_a"."name" asc',
                    'bindings' => [
                        12,
                        'b',
                        56,
                    ],
                ],
                static function (): EloquentBuilder {
                    return SortBuilderTest__ModelA::query();
                },
                [
                    [
                        'hasOneB' => ['name' => 'asc'],
                    ],
                    [
                        'hasOneB' => ['created_at' => 'desc'],
                    ],
                    [
                        'hasOneB' => [
                            'hasOneC' => ['name' => 'desc'],
                        ],
                    ],
                    [
                        'hasOneB' => [
                            'hasOneC' => ['created_at' => 'desc'],
                        ],
                    ],
                    [
                        'name' => 'asc',
                    ],
                ],
            ],
            'eloquent: '.MorphOne::class            => [
                [
                    'sql'      => ''.
                        'select'.
                        ' "table_a".*,'.
                        ' "table_alias_0"."name" as "table_alias_0_name",'.
                        ' "table_alias_0"."created_at" as "table_alias_0_created_at",'.
                        ' "table_alias_1"."name" as "table_alias_1_name",'.
                        ' "table_alias_1"."created_at" as "table_alias_1_created_at" '.
                        'from "table_a" '.
                        'left join (select * from "table_b" where'.
                        ' "table_b"."morphable_a_id" = ? and "table_b"."morphable_a_id" is not null and'.
                        ' "table_b"."morphable_a_type" = ? and "c" = ?'.
                        ') as "table_alias_0" on "table_alias_0"."morphable_a_id" = "table_a"."id" '.
                        'left join (select * from "table_c" where'.
                        ' "table_c"."morphable_b_id" = ? and "table_c"."morphable_b_id" is not null and'.
                        ' "table_c"."morphable_b_type" = ?'.
                        ') as "table_alias_1" on "table_alias_1"."morphable_b_id" = "table_alias_0"."id" '.
                        'order by'.
                        ' "table_alias_0_name" asc,'.
                        ' "table_alias_0_created_at" desc,'.
                        ' "table_alias_1_name" desc,'.
                        ' "table_alias_1_created_at" desc,'.
                        ' "table_a"."name" asc',
                    'bindings' => [
                        12,
                        SortBuilderTest__ModelA::class,
                        'c',
                        56,
                        SortBuilderTest__ModelB::class,
                    ],
                ],
                static function (): EloquentBuilder {
                    return SortBuilderTest__ModelA::query();
                },
                [
                    [
                        'morphOneB' => ['name' => 'asc'],
                    ],
                    [
                        'morphOneB' => ['created_at' => 'desc'],
                    ],
                    [
                        'morphOneB' => [
                            'morphOneC' => ['name' => 'desc'],
                        ],
                    ],
                    [
                        'morphOneB' => [
                            'morphOneC' => ['created_at' => 'desc'],
                        ],
                    ],
                    [
                        'name' => 'asc',
                    ],
                ],
            ],
        ];
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
}
