<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Complex;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use LastDragon_ru\LaraASP\Eloquent\Exceptions\PropertyIsNotRelation;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\Client\SearchConditionTooManyOperators;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQL\Utils\Property;
use Nuwave\Lighthouse\Execution\Arguments\Argument;

use function is_array;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Complex\Relation
 */
class RelationTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::call
     *
     * @dataProvider dataProviderCall
     *
     * @param array{query: string, bindings: array<mixed>}|Exception $expected
     * @param Closure(static): object                                $builderFactory
     * @param Closure(static): Argument                              $argumentFactory
     */
    public function testCall(
        array|Exception $expected,
        Closure $builderFactory,
        Property $property,
        Closure $argumentFactory,
    ): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $operator = $this->app->make(Relation::class);
        $argument = $argumentFactory($this);
        $search   = $this->app->make(Directive::class);
        $builder  = $builderFactory($this);
        $builder  = $operator->call($search, $builder, $property, $argument);

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
    public function dataProviderCall(): array {
        $graphql = <<<'GRAPHQL'
            input TestInput {
                property: TestOperators
            }

            input TestOperators {
                lessThan: Int
                @searchByOperatorLessThan

                equal: Int
                @searchByOperatorEqual

                notEqual: Int
                @searchByOperatorNotEqual
            }

            input TestRelation {
                where: TestInput

                count: TestOperators

                exists: Boolean

                notExists: Boolean! = false
            }

            type Query {
                test(input: TestInput): Int @all
            }
        GRAPHQL;

        return [
            'not a relation'                          => [
                new PropertyIsNotRelation(new RelationTest__ModelA(), 'delete'),
                static function (): EloquentBuilder {
                    return RelationTest__ModelA::query();
                },
                new Property('delete'),
                static function (self $test) use ($graphql): Argument {
                    return $test->getGraphQLArgument(
                        'TestRelation!',
                        [
                            'notExists' => true,
                        ],
                        $graphql,
                    );
                },
            ],
            '{exists: true}'                          => [
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
                new Property('test'),
                static function (self $test) use ($graphql): Argument {
                    return $test->getGraphQLArgument(
                        'TestRelation!',
                        [
                            'exists' => true,
                        ],
                        $graphql,
                    );
                },
            ],
            '{notExists: true}'                       => [
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
                new Property('test'),
                static function (self $test) use ($graphql): Argument {
                    return $test->getGraphQLArgument(
                        'TestRelation',
                        [
                            'notExists' => true,
                        ],
                        $graphql,
                    );
                },
            ],
            '{relation: {property: {equal: 1}}}'      => [
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
                new Property('test'),
                static function (self $test) use ($graphql): Argument {
                    return $test->getGraphQLArgument(
                        'TestRelation',
                        [
                            'where' => [
                                'property' => [
                                    'equal' => 123,
                                ],
                            ],
                        ],
                        $graphql,
                    );
                },
            ],
            '{count: {equal: 1}}'                     => [
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
                new Property('test'),
                static function (self $test) use ($graphql): Argument {
                    return $test->getGraphQLArgument(
                        'TestRelation',
                        [
                            'count' => [
                                'equal' => 345,
                            ],
                        ],
                        $graphql,
                    );
                },
            ],
            '{count: { multiple operators }}'         => [
                new SearchConditionTooManyOperators(['equal', 'lt']),
                static function (): EloquentBuilder {
                    return RelationTest__ModelA::query();
                },
                new Property('test'),
                static function (self $test) use ($graphql): Argument {
                    return $test->getGraphQLArgument(
                        'TestRelation',
                        [
                            'count' => [
                                'equal'    => 345,
                                'lessThan' => 1,
                            ],
                        ],
                        $graphql,
                    );
                },
            ],
            '{where: {{property: {equal: 1}}}} (own)' => [
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
                new Property('a'),
                static function (self $test) use ($graphql): Argument {
                    return $test->getGraphQLArgument(
                        'TestRelation',
                        [
                            'where' => [
                                'property' => [
                                    'equal' => 123,
                                ],
                            ],
                        ],
                        $graphql,
                    );
                },
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
