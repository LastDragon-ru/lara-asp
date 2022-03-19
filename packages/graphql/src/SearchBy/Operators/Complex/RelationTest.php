<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Complex;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\Eloquent\Exceptions\PropertyIsNotRelation;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\BuilderUnsupported;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\Client\SearchConditionTooManyOperators;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\Equal;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\NotEqual;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\SearchBuilder;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;

use function is_array;

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
     * @param array{query: string, bindings: array<mixed>}|Exception $expected
     * @param array<mixed>                                           $conditions
     */
    public function testApply(
        array|Exception $expected,
        Closure $builder,
        string $property,
        array $conditions = [],
    ): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $search   = new SearchBuilder([
            $this->app->make(Equal::class),
            $this->app->make(NotEqual::class),
            $this->app->make(Relation::class),
        ]);
        $relation = $this->app->make(Relation::class);
        $builder  = $builder($this);
        $builder  = $relation->apply($search, $builder, $property, $conditions);

        if (is_array($expected)) {
            self::assertDatabaseQueryEquals($expected, $builder);
        } else {
            self::fail('Something wrong...');
        }
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
                new BuilderUnsupported(QueryBuilder::class),
                static function (self $test): QueryBuilder {
                    return $test->app->make('db')->table('tmp');
                },
                'test',
                [],
            ],
            'not a relation'                                         => [
                new PropertyIsNotRelation(new RelationTest__ModelA(), 'delete'),
                static function (): EloquentBuilder {
                    return RelationTest__ModelA::query();
                },
                'delete',
                [],
            ],
            '{relation: yes}'                                        => [
                [
                    'query'    => 'select * from "table_a" where exists ('.
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
            '{relation: yes, exists: true}'                          => [
                [
                    'query'    => 'select * from "table_a" where exists ('.
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
                    'exists'   => true,
                ],
            ],
            '{relation: yes, notExists: true}'                       => [
                [
                    'query'    => 'select * from "table_a" where not exists ('.
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
                    'relation'  => 'yes',
                    'notExists' => true,
                ],
            ],
            '{relation: {property: {equal: 1}}}'                     => [
                [
                    'query'    => 'select * from "table_a" where exists ('.
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
                    'query'    => 'select * from "table_a" where ('.
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
                new SearchConditionTooManyOperators(['equal', 'lt']),
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
                    'query'    => 'select * from "table_a" where exists ('.
                        'select * from "table_a" as "laravel_reserved_0" where '.
                        '"table_a"."id" = "laravel_reserved_0"."relation_test___model_a_id" '.
                        'and "laravel_reserved_0"."property" = ?'.
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

    /**
     * @return HasOne<RelationTest__ModelB>
     */
    public function test(): HasOne {
        return $this->hasOne(RelationTest__ModelB::class, 'table_a_id');
    }

    /**
     * @return HasOne<static>
     */
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
