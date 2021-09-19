<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\Eloquent\Exceptions\PropertyIsNotRelation;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\BuilderUnsupported;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\Client\SortClauseEmpty;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\Client\SortClauseTooManyProperties;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\RelationUnsupported;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\QueryBuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\MergeDataProvider;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SortBy\DatabaseBuilder
 */
class DatabaseBuilderTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::build
     *
     * @dataProvider dataProviderBuild
     *
     * @param array<mixed>|Exception $expected
     * @param array<mixed>           $clause
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
        return (new MergeDataProvider([
            'Both'     => (new CompositeDataProvider(
                new BuilderDataProvider(),
                new ArrayDataProvider([
                    'empty'                  => [
                        [
                            'sql'      => 'select * from "tmp"',
                            'bindings' => [],
                        ],
                        [],
                    ],
                    'empty clause'           => [
                        new SortClauseEmpty(),
                        [
                            [],
                        ],
                    ],
                    'more than one property' => [
                        new SortClauseTooManyProperties(['a', 'b']),
                        [
                            [
                                'a' => 'asc',
                                'b' => 'asc',
                            ],
                        ],
                    ],
                ]),
            )),
            'Query'    => (new CompositeDataProvider(
                new QueryBuilderDataProvider(),
                new ArrayDataProvider([
                    'simple condition'            => [
                        [
                            'sql'      => 'select * from "tmp" order by "a" asc',
                            'bindings' => [],
                        ],
                        [
                            [
                                'a' => 'asc',
                            ],
                        ],
                    ],
                    'query builder not supported' => [
                        new BuilderUnsupported(QueryBuilder::class),
                        [
                            [
                                'test' => ['name' => 'asc'],
                            ],
                        ],
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
                        [
                            'delete' => ['name' => 'asc'],
                        ],
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
                        [
                            'unsupported' => ['name' => 'asc'],
                        ],
                    ],
                ],
                'simple condition'   => [
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
                BelongsTo::class     => [
                    [
                        'sql'      => ''.
                            'select'.
                            ' "table_a".*,'.
                            ' "table_alias_0"."name" as "table_alias_0_name",'.
                            ' "table_alias_0"."created_at" as "table_alias_0_created_at",'.
                            ' "table_alias_1"."name" as "table_alias_1_name",'.
                            ' "table_alias_1"."created_at" as "table_alias_1_created_at" '.
                            'from "table_a" '.
                            'left join (select * from "table_b" where "a" = ?)'.
                            ' as "table_alias_0" on "table_alias_0"."id" = "table_a"."belongs_to_b_id" '.
                            'left join (select * from "table_c")'.
                            ' as "table_alias_1" on "table_alias_1"."id" = "table_alias_0"."belongs_to_c_id" '.
                            'order by'.
                            ' "table_alias_0_name" asc,'.
                            ' "table_alias_0_created_at" desc,'.
                            ' "table_alias_1_name" desc,'.
                            ' "table_alias_1_created_at" desc,'.
                            ' "table_a"."name" asc',
                        'bindings' => [
                            'a',
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
                HasOne::class        => [
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
                            ' "b" = ?'.
                            ') as "table_alias_0" on "table_alias_0"."model_a_id" = "table_a"."id" '.
                            'left join (select * from "table_c"'.
                            ') as "table_alias_1" on "table_alias_1"."model_b_id" = "table_alias_0"."id" '.
                            'order by'.
                            ' "table_alias_0_name" asc,'.
                            ' "table_alias_0_created_at" desc,'.
                            ' "table_alias_1_name" desc,'.
                            ' "table_alias_1_created_at" desc,'.
                            ' "table_a"."name" asc',
                        'bindings' => [
                            'b',
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
                MorphOne::class      => [
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
                            ' "c" = ?'.
                            ') as "table_alias_0" on "table_alias_0"."morphable_a_id" = "table_a"."id" and'.
                            ' "table_alias_0"."morphable_a_type" = ? '.
                            'left join (select * from "table_c"'.
                            ') as "table_alias_1" on "table_alias_1"."morphable_b_id" = "table_alias_0"."id" and'.
                            ' "table_alias_1"."morphable_b_type" = ? '.
                            'order by'.
                            ' "table_alias_0_name" asc,'.
                            ' "table_alias_0_created_at" desc,'.
                            ' "table_alias_1_name" desc,'.
                            ' "table_alias_1_created_at" desc,'.
                            ' "table_a"."name" asc',
                        'bindings' => [
                            'c',
                            SortBuilderTest__ModelA::class,
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
                HasOneThrough::class => [
                    [
                        'sql'      => ''.
                            'select'.
                            ' "table_a".*,'.
                            ' "table_alias_0"."name" as "table_alias_0_name",'.
                            ' "table_alias_0"."created_at" as "table_alias_0_created_at",'.
                            ' "table_alias_1"."name" as "table_alias_1_name",'.
                            ' "table_alias_1"."created_at" as "table_alias_1_created_at" '.
                            'from "table_a" '.
                            'left join ('.
                            'select'.
                            ' "table_b"."id" as "table_alias_0_id",'.
                            ' "table_c".*'.' '.
                            'from "table_c" '.
                            'inner join "table_b" on "table_b"."second_local_key" = "table_c"."second_key"'.
                            ') as "table_alias_0" on "table_alias_0"."table_alias_0_id" = "table_a"."local_key" '.
                            'left join ('.
                            'select'.
                            ' "table_b"."id" as "table_alias_1_id",'.
                            ' "table_a".* '.
                            'from "table_a" '.
                            'inner join "table_b" on "table_b"."second_local_key" = "table_a"."second_key"'.
                            ') as "table_alias_1" on "table_alias_1"."table_alias_1_id" = "table_alias_0"."local_key" '.
                            'order by'.
                            ' "table_alias_0_name" asc,'.
                            ' "table_alias_0_created_at" desc,'.
                            ' "table_alias_1_name" desc,'.
                            ' "table_alias_1_created_at" desc,'.
                            ' "table_a"."name" asc',
                        'bindings' => [
                            // empty
                        ],
                    ],
                    static function (): EloquentBuilder {
                        return SortBuilderTest__ModelA::query();
                    },
                    [
                        [
                            'hasOneThroughC' => ['name' => 'asc'],
                        ],
                        [
                            'hasOneThroughC' => ['created_at' => 'desc'],
                        ],
                        [
                            'hasOneThroughC' => [
                                'hasOneThroughA' => ['name' => 'desc'],
                            ],
                        ],
                        [
                            'hasOneThroughC' => [
                                'hasOneThroughA' => ['created_at' => 'desc'],
                            ],
                        ],
                        [
                            'name' => 'asc',
                        ],
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
