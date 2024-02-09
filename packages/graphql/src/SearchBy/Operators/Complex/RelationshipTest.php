<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Complex;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use LastDragon_ru\LaraASP\Eloquent\Exceptions\PropertyIsNotRelation;
use LastDragon_ru\LaraASP\GraphQL\Builder\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionTooManyOperators;
use LastDragon_ru\LaraASP\GraphQL\Builder\Field;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\User;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\OperatorTests;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use PHPUnit\Framework\Attributes\CoversClass;

use function implode;

/**
 * @internal
 *
 * @phpstan-import-type BuilderFactory from BuilderDataProvider
 */
#[CoversClass(Relationship::class)]
final class RelationshipTest extends TestCase {
    use OperatorTests;

    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderCall
     *
     * @param array{query: string, bindings: array<array-key, mixed>}|Exception $expected
     * @param BuilderFactory                                                    $builderFactory
     * @param Closure(static): Argument                                         $argumentFactory
     * @param Closure(static): Context|null                                     $contextFactory
     * @param Closure(object, Field): string|null                               $resolver
     */
    public function testCall(
        array|Exception $expected,
        Closure $builderFactory,
        Field $field,
        Closure $argumentFactory,
        ?Closure $contextFactory,
        ?Closure $resolver,
    ): void {
        $this->testOperator(
            Directive::class,
            $expected,
            $builderFactory,
            $field,
            $argumentFactory,
            $contextFactory,
            $resolver,
        );
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<array-key, mixed>
     */
    public static function dataProviderCall(): array {
        $graphql = <<<'GRAPHQL'
            input TestInput {
                field: TestOperators
                @searchByOperatorCondition

                user: TestRelation
                @searchByOperatorRelationship
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
            'not a relation'                              => [
                new PropertyIsNotRelation(new User(), 'delete'),
                static function (): EloquentBuilder {
                    return User::query();
                },
                new Field('delete'),
                static function (self $test) use ($graphql): Argument {
                    return $test->getGraphQLArgument(
                        'TestRelation!',
                        [
                            'notExists' => true,
                        ],
                        $graphql,
                    );
                },
                null,
                null,
            ],
            '{exists: true}'                              => [
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
                new Field('car'),
                static function (self $test) use ($graphql): Argument {
                    return $test->getGraphQLArgument(
                        'TestRelation!',
                        [
                            'exists' => true,
                        ],
                        $graphql,
                    );
                },
                null,
                null,
            ],
            '{notExists: true}'                           => [
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
                new Field('car'),
                static function (self $test) use ($graphql): Argument {
                    return $test->getGraphQLArgument(
                        'TestRelation',
                        [
                            'notExists' => true,
                        ],
                        $graphql,
                    );
                },
                null,
                null,
            ],
            '{relation: {field: {equal: 1}}}'             => [
                [
                    'query'    => <<<'SQL'
                        select * from "users" where exists (
                            select * from "cars"
                            where "users"."localKey" = "cars"."foreignKey"
                                and "cars"."field" = ?
                                and "favorite" = ?
                        )
                    SQL
                    ,
                    'bindings' => [123, 1],
                ],
                static function (): EloquentBuilder {
                    return User::query();
                },
                new Field('car'),
                static function (self $test) use ($graphql): Argument {
                    return $test->getGraphQLArgument(
                        'TestRelation',
                        [
                            'where' => [
                                'field' => [
                                    'equal' => 123,
                                ],
                            ],
                        ],
                        $graphql,
                    );
                },
                null,
                null,
            ],
            '{count: {equal: 1}}'                         => [
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
                new Field('car'),
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
                null,
                null,
            ],
            '{count: { multiple operators }}'             => [
                new ConditionTooManyOperators(['lessThan', 'equal']),
                static function (): EloquentBuilder {
                    return User::query();
                },
                new Field('car'),
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
                null,
                null,
            ],
            '{where: {{field: {equal: 1}}}} (own)'        => [
                [
                    'query'    => <<<'SQL'
                        select * from "users" where exists (
                            select *
                            from "users" as "laravel_reserved_0"
                            where "users"."localKey" = "laravel_reserved_0"."foreignKey"
                                and "laravel_reserved_0"."field" = ?
                        )
                    SQL
                    ,
                    'bindings' => [123],
                ],
                static function (): EloquentBuilder {
                    return User::query();
                },
                new Field('parent'),
                static function (self $test) use ($graphql): Argument {
                    return $test->getGraphQLArgument(
                        'TestRelation',
                        [
                            'where' => [
                                'field' => [
                                    'equal' => 123,
                                ],
                            ],
                        ],
                        $graphql,
                    );
                },
                null,
                null,
            ],
            '{relation: {relation: {field: {equal: 1}}}}' => [
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
                                            and "users"."field" = ?
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
                new Field('car'),
                static function (self $test) use ($graphql): Argument {
                    return $test->getGraphQLArgument(
                        'TestRelation',
                        [
                            'where' => [
                                'user' => [
                                    'where' => [
                                        'field' => [
                                            'equal' => 123,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        $graphql,
                    );
                },
                null,
                null,
            ],
            'resolver'                                    => [
                [
                    'query'    => <<<'SQL'
                        select * from "users" where exists (
                            select *
                            from "users" as "laravel_reserved_0"
                            where "users"."localKey" = "laravel_reserved_0"."foreignKey"
                                and "laravel_reserved_0"."resolved__field" = ?
                        )
                    SQL
                    ,
                    'bindings' => [123],
                ],
                static function (): EloquentBuilder {
                    return User::query();
                },
                new Field('parent'),
                static function (self $test) use ($graphql): Argument {
                    return $test->getGraphQLArgument(
                        'TestRelation',
                        [
                            'where' => [
                                'field' => [
                                    'equal' => 123,
                                ],
                            ],
                        ],
                        $graphql,
                    );
                },
                null,
                static function (object $builder, Field $field): string {
                    return implode('.', $field->getParent()->getPath()).'.resolved__'.$field->getName();
                },
            ],
        ];
    }
    // </editor-fold>
}
