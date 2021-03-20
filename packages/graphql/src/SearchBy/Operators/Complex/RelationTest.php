<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Complex;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\Equal;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Not;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\SearchBuilder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\SearchLogicException;
use LastDragon_ru\LaraASP\GraphQL\Testing\TestCase;

use function preg_replace;
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
        string $tableAlias = null,
    ): void {
        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $search   = new SearchBuilder([
            $this->app->make(Not::class),
            $this->app->make(Equal::class),
            $this->app->make(Relation::class),
        ]);
        $relation = $this->app->make(Relation::class);
        $builder  = $builder($this);
        $builder  = $relation->apply($search, $builder, $property, $conditions, $tableAlias);
        $actual   = [
            'sql'      => preg_replace('/laravel_reserved_[\d]+/', 'laravel_reserved', $builder->toSql()),
            'bindings' => $builder->getBindings(),
        ];

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
            'query builder not supported' => [
                new SearchLogicException(sprintf(
                    'Operator `%s` can not be used with `%s`.',
                    (new Relation())->getName(),
                    QueryBuilder::class,
                )),
                static function (self $test): QueryBuilder {
                    return $test->app->make('db')->table('tmp');
                },
                'test',
                [],
            ],
            'not a relation'              => [
                new SearchLogicException(sprintf(
                    'Property `%s` is not a relation.',
                    'delete',
                )),
                static function (): EloquentBuilder {
                    return RelationTest__Model::query();
                },
                'delete',
                [],
            ],
            '{has: yes}'                  => [
                [
                    'sql'      => 'select * from "tmp" where exists ('.
                        'select * from "tmp" as "laravel_reserved" '.
                        'where "tmp"."id" = "laravel_reserved"."relation_test___model_id"'.
                        ')',
                    'bindings' => [],
                ],
                static function (): EloquentBuilder {
                    return RelationTest__Model::query();
                },
                'test',
                [
                    'has' => 'yes',
                ],
            ],
            '{has: yes, not: yes}'        => [
                [
                    'sql'      => 'select * from "tmp" where not exists ('.
                        'select * from "tmp" as "laravel_reserved" '.
                        'where "tmp"."id" = "laravel_reserved"."relation_test___model_id"'.
                        ')',
                    'bindings' => [],
                ],
                static function (): EloquentBuilder {
                    return RelationTest__Model::query();
                },
                'test',
                [
                    'has' => 'yes',
                    'not' => 'yes',
                ],
            ],
            '{has: {property: {eq: 1}}}'  => [
                [
                    'sql'      => 'select * from "tmp" where exists ('.
                        'select * from "tmp" as "laravel_reserved" where '.
                        '"tmp"."id" = "laravel_reserved"."relation_test___model_id" '.
                        'and "laravel_reserved"."property" = ?'.
                        ')',
                    'bindings' => [123],
                ],
                static function (): EloquentBuilder {
                    return RelationTest__Model::query();
                },
                'test',
                [
                    'has' => [
                        'property' => [
                            'eq' => 123,
                        ],
                    ],
                ],
            ],
            '{has: yes, eq: 1}'           => [
                [
                    'sql'      => 'select * from "tmp" where ('.
                        'select count(*) from "tmp" as "laravel_reserved" where '.
                        '"tmp"."id" = "laravel_reserved"."relation_test___model_id"'.
                        ') = 345',
                    'bindings' => [/* strange */],
                ],
                static function (): EloquentBuilder {
                    return RelationTest__Model::query();
                },
                'test',
                [
                    'has' => 'yes',
                    'eq'  => 345,
                ],
            ],
            '{has: yes, eq: 1, not: yes}' => [
                [
                    'sql'      => 'select * from "tmp" where ('.
                        'select count(*) from "tmp" as "laravel_reserved" '.
                        'where "tmp"."id" = "laravel_reserved"."relation_test___model_id"'.
                        ') != 345',
                    'bindings' => [/* strange */],
                ],
                static function (): EloquentBuilder {
                    return RelationTest__Model::query();
                },
                'test',
                [
                    'has' => 'yes',
                    'not' => 'yes',
                    'eq'  => 345,
                ],
            ],
            '{has: yes, eq: 1, gt: 2}'    => [
                new SearchLogicException(
                    'Only one comparison operator allowed, found: `eq`, `gt`',
                ),
                static function (): EloquentBuilder {
                    return RelationTest__Model::query();
                },
                'test',
                [
                    'has' => 'yes',
                    'eq'  => 345,
                    'gt'  => 2,
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
class RelationTest__Model extends Model {
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     *
     * @var string
     */
    public $table = 'tmp';

    public function test(): HasOne {
        return $this->hasOne(static::class);
    }
}
