<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Complex;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\PackageTranslator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\Equal;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\NotEqual;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\SearchBuilder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\SearchLogicException;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LogicException;

use function sprintf;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Complex\Relation
 */
class RelationTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::apply
     *
     * @dataProvider dataProviderApply
     *
     * @param array{sql: string, bindings: array<mixed>}|\Exception $expected
     * @param array<mixed> $conditions
     */
    public function testApply(
        array|Exception $expected,
        Closure $builder,
        string $property,
        array $conditions = [],
    ): void {
        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $search   = new SearchBuilder(
            $this->app->make(PackageTranslator::class),
            [
                $this->app->make(Equal::class),
                $this->app->make(NotEqual::class),
                $this->app->make(Relation::class),
            ],
        );
        $relation = $this->app->make(Relation::class);
        $builder  = $builder($this);
        $builder  = $relation->apply($search, $builder, $property, $conditions);
        $actual   = $this->getSql($builder);

        $this->assertEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    public function dataProviderApply(): array {
        return [
            'query builder not supported'                            => [
                new SearchLogicException(sprintf(
                    'Operator `%s` cannot be used with `%s`.',
                    'relation',
                    QueryBuilder::class,
                )),
                static function (self $test): QueryBuilder {
                    return $test->app->make('db')->table('tmp');
                },
                'test',
                [],
            ],
            'not a relation'                                         => [
                new LogicException(sprintf(
                    'Property `%s` is not a relation.',
                    'delete',
                )),
                static function (): EloquentBuilder {
                    return RelationTest__ModelA::query();
                },
                'delete',
                [],
            ],
            '{relation: yes}'                                        => [
                [
                    'sql'      => 'select * from "table_a" where exists ('.
                        'select * from "table_b" '.
                        'where "table_a"."id" = "table_b"."table_a_id"'.
                        ')',
                    'bindings' => [],
                ],
                static function (): EloquentBuilder {
                    return RelationTest__ModelA::query();
                },
                'test',
                [
                    'relation' => 'yes',
                ],
            ],
            '{relation: yes, not: yes}'                              => [
                [
                    'sql'      => 'select * from "table_a" where not exists ('.
                        'select * from "table_b" '.
                        'where "table_a"."id" = "table_b"."table_a_id"'.
                        ')',
                    'bindings' => [],
                ],
                static function (): EloquentBuilder {
                    return RelationTest__ModelA::query();
                },
                'test',
                [
                    'relation' => 'yes',
                    'not'      => true,
                ],
            ],
            '{relation: {property: {equal: 1}}}'                     => [
                [
                    'sql'      => 'select * from "table_a" where exists ('.
                        'select * from "table_b" where '.
                        '"table_a"."id" = "table_b"."table_a_id" and "table_b"."property" = ?'.
                        ')',
                    'bindings' => [123],
                ],
                static function (): EloquentBuilder {
                    return RelationTest__ModelA::query();
                },
                'test',
                [
                    'where' => [
                        'property' => [
                            'equal' => 123,
                        ],
                    ],
                ],
            ],
            '{relation: yes, count: {equal: 1}}'                     => [
                [
                    'sql'      => 'select * from "table_a" where ('.
                        'select count(*) from "table_b" where '.
                        '"table_a"."id" = "table_b"."table_a_id"'.
                        ') = 345',
                    'bindings' => [/* strange */],
                ],
                static function (): EloquentBuilder {
                    return RelationTest__ModelA::query();
                },
                'test',
                [
                    'relation' => 'yes',
                    'count'    => [
                        'equal' => 345,
                    ],
                ],
            ],
            '{relation: yes, count: { multiple operators }}'         => [
                new SearchLogicException(
                    'Only one comparison operator allowed, found: `equal`, `lt`',
                ),
                static function (): EloquentBuilder {
                    return RelationTest__ModelA::query();
                },
                'test',
                [
                    'relation' => 'yes',
                    'count'    => [
                        'equal' => 345,
                        'lt'    => 1,
                    ],
                ],
            ],
            '{relation: yes, where: {{property: {equal: 1}}}} (own)' => [
                [
                    'sql'      => 'select * from "table_a" where exists ('.
                        'select * from "table_a" as "table_alias_0" where '.
                        '"table_a"."id" = "table_alias_0"."relation_test___model_a_id" '.
                        'and "table_alias_0"."property" = ?'.
                        ')',
                    'bindings' => [123],
                ],
                static function (): EloquentBuilder {
                    return RelationTest__ModelA::query();
                },
                'a',
                [
                    'relation' => 'yes',
                    'where'    => [
                        'property' => [
                            'equal' => 123,
                        ],
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
class RelationTest__ModelA extends Model {
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     *
     * @var string
     */
    public $table = 'table_a';

    public function test(): HasOne {
        return $this->hasOne(RelationTest__ModelB::class, 'table_a_id');
    }

    public function a(): HasOne {
        return $this->hasOne(static::class);
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class RelationTest__ModelB extends Model {
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     *
     * @var string
     */
    public $table = 'table_b';
}
