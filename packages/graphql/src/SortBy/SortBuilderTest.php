<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\Testing\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\TestCase;
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

        $directive = $this->app->make(SortByDirective::class);
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
                    'Relation can not be used with `%s`.',
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
                        'left join "table_b" as "table_alias_0"'.
                        ' on "table_alias_0"."id" = "table_a"."belongs_to_b_id" '.
                        'left join "table_c" as "table_alias_1"'.
                        ' on "table_alias_1"."id" = "table_alias_0"."belongs_to_c_id" '.
                        'order by'.
                        ' "table_alias_0_name" asc,'.
                        ' "table_alias_0_created_at" desc,'.
                        ' "table_alias_1_name" desc,'.
                        ' "table_alias_1_created_at" desc,'.
                        ' "table_a"."name" asc',
                    'bindings' => [],
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
                        'left join "table_b" as "table_alias_0"'.
                        ' on "table_alias_0"."model_a_id" = "table_a"."id" '.
                        'left join "table_c" as "table_alias_1"'.
                        ' on "table_alias_1"."model_b_id" = "table_alias_0"."id" '.
                        'order by'.
                        ' "table_alias_0_name" asc,'.
                        ' "table_alias_0_created_at" desc,'.
                        ' "table_alias_1_name" desc,'.
                        ' "table_alias_1_created_at" desc,'.
                        ' "table_a"."name" asc',
                    'bindings' => [],
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

    public function belongsToB(): BelongsTo {
        return $this->belongsTo(SortBuilderTest__ModelB::class);
    }

    public function hasOneB(): HasOne {
        return $this->hasOne(SortBuilderTest__ModelB::class, 'model_a_id');
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

    public function belongsToC(): BelongsTo {
        return $this->belongsTo(SortBuilderTest__ModelC::class);
    }

    public function hasOneC(): HasOne {
        return $this->hasOne(SortBuilderTest__ModelC::class, 'model_b_id');
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
}
