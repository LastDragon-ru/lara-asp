<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Complex;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use LastDragon_ru\LaraASP\Eloquent\Exceptions\PropertyIsNotRelation;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionTooManyOperators;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\User;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Nuwave\Lighthouse\Execution\Arguments\Argument;

use function is_array;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Complex\Relation
 *
 * @phpstan-import-type BuilderFactory from BuilderDataProvider
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
     * @param BuilderFactory                                         $builderFactory
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
                @searchByProperty

                user: TestRelation
                @searchByOperatorRelation
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
            'not a relation'                                 => [
                new PropertyIsNotRelation(new User(), 'delete'),
                static function (): EloquentBuilder {
                    return User::query();
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
            '{exists: true}'                                 => [
                [
                    'query'    => 'select * from "users" where exists ('.
                        'select * from "cars" '.
                        'where "users"."localKey" = "cars"."foreignKey" and "favorite" = ?'.
                        ')',
                    'bindings' => [1],
                ],
                static function (): EloquentBuilder {
                    return User::query();
                },
                new Property('car'),
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
            '{notExists: true}'                              => [
                [
                    'query'    => 'select * from "users" where not exists ('.
                        'select * from "cars" '.
                        'where "users"."localKey" = "cars"."foreignKey" and "favorite" = ?'.
                        ')',
                    'bindings' => [1],
                ],
                static function (): EloquentBuilder {
                    return User::query();
                },
                new Property('car'),
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
            '{relation: {property: {equal: 1}}}'             => [
                [
                    'query'    => <<<'SQL'
                        select * from "users" where exists (
                            select * from "cars"
                            where "users"."localKey" = "cars"."foreignKey"
                                and "cars"."property" = ?
                                and "favorite" = ?
                        )
                    SQL
                    ,
                    'bindings' => [123, 1],
                ],
                static function (): EloquentBuilder {
                    return User::query();
                },
                new Property('car'),
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
            '{count: {equal: 1}}'                            => [
                [
                    'query'    => <<<'SQL'
                        select * from "users" where (
                            select count(*)
                            from "cars"
                            where "users"."localKey" = "cars"."foreignKey"
                                and "favorite" = ?
                        ) = 345
                    SQL
                    ,
                    'bindings' => [1],
                ],
                static function (): EloquentBuilder {
                    return User::query();
                },
                new Property('car'),
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
            '{count: { multiple operators }}'                => [
                new ConditionTooManyOperators(['lessThan', 'equal']),
                static function (): EloquentBuilder {
                    return User::query();
                },
                new Property('car'),
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
            '{where: {{property: {equal: 1}}}} (own)'        => [
                [
                    'query'    => <<<'SQL'
                        select * from "users" where exists (
                            select *
                            from "users" as "laravel_reserved_0"
                            where "users"."localKey" = "laravel_reserved_0"."foreignKey"
                                and "laravel_reserved_0"."property" = ?
                        )
                    SQL
                    ,
                    'bindings' => [123],
                ],
                static function (): EloquentBuilder {
                    return User::query();
                },
                new Property('parent'),
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
            '{relation: {relation: {property: {equal: 1}}}}' => [
                [
                    'query'    => <<<'SQL'
                        select
                            *
                        from
                            "users"
                        where
                            exists (
                                select
                                    *
                                from
                                    "cars"
                                where
                                    "users"."localKey" = "cars"."foreignKey"
                                    and exists (
                                        select
                                            *
                                        from
                                            "users"
                                        where
                                            "cars"."foreignKey" = "users"."ownerKey"
                                            and "users"."property" = ?
                                            and "deleted_at" is null
                                    )
                                    and "favorite" = ?
                            )
                    SQL
                    ,
                    'bindings' => [123, 1],
                ],
                static function (): EloquentBuilder {
                    return User::query();
                },
                new Property('car'),
                static function (self $test) use ($graphql): Argument {
                    return $test->getGraphQLArgument(
                        'TestRelation',
                        [
                            'where' => [
                                'user' => [
                                    'where' => [
                                        'property' => [
                                            'equal' => 123,
                                        ],
                                    ],
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
