<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical;

use Closure;
use LastDragon_ru\LaraASP\GraphQL\Builder\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Field;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\EloquentBuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\QueryBuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\OperatorTests;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\MergeDataProvider;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use PHPUnit\Framework\Attributes\CoversClass;

use function implode;

/**
 * @internal
 *
 * @phpstan-import-type BuilderFactory from BuilderDataProvider
 */
#[CoversClass(AnyOf::class)]
final class AnyOfTest extends TestCase {
    use OperatorTests;

    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderCall
     *
     * @param array{query: string, bindings: array<array-key, mixed>} $expected
     * @param BuilderFactory                                          $builderFactory
     * @param Closure(static): Argument                               $argumentFactory
     * @param Closure(static): Context|null                           $contextFactory
     * @param Closure(object, Field): string|null                     $resolver
     */
    public function testCall(
        array $expected,
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
        $factory = static function (self $test): Argument {
            return $test->getGraphQLArgument(
                '[TestInput!]',
                [
                    ['a' => ['equal' => 2]],
                    ['b' => ['notEqual' => 22]],
                ],
                <<<'GRAPHQL'
                    input TestInput {
                        a: TestOperators
                        @searchByOperatorCondition

                        b: TestOperators
                        @searchByOperatorCondition
                    }

                    input TestOperators {
                        equal: Int
                        @searchByOperatorEqual

                        notEqual: Int
                        @searchByOperatorNotEqual
                    }

                    type Query {
                        test(input: TestInput): Int @all
                    }
                GRAPHQL,
            );
        };

        return (new MergeDataProvider([
            'Query'    => new CompositeDataProvider(
                new QueryBuilderDataProvider(),
                new ArrayDataProvider([
                    'field'      => [
                        [
                            'query'    => 'select * from "test_objects" where (("a" = ?) or ("b" != ?))',
                            'bindings' => [
                                2,
                                22,
                            ],
                        ],
                        new Field('operator name should be ignored'),
                        $factory,
                        null,
                        null,
                    ],
                    'with alias' => [
                        [
                            'query'    => <<<'SQL'
                                select * from "test_objects" where (("alias"."a" = ?) or ("alias"."b" != ?))
                            SQL
                            ,
                            'bindings' => [
                                2,
                                22,
                            ],
                        ],
                        new Field('alias', 'operator name should be ignored'),
                        $factory,
                        null,
                        null,
                    ],
                    'resolver'   => [
                        [
                            'query'    => <<<'SQL'
                                select * from "test_objects" where (("alias__a" = ?) or ("alias__b" != ?))
                            SQL
                            ,
                            'bindings' => [
                                2,
                                22,
                            ],
                        ],
                        new Field('alias', 'operator name should be ignored'),
                        $factory,
                        null,
                        static function (object $builder, Field $field): string {
                            return implode('__', $field->getPath());
                        },
                    ],
                ]),
            ),
            'Eloquent' => new CompositeDataProvider(
                new EloquentBuilderDataProvider(),
                new ArrayDataProvider([
                    'field'      => [
                        [
                            'query'    => <<<'SQL'
                                select *
                                from "test_objects"
                                where (("test_objects"."a" = ?) or ("test_objects"."b" != ?))
                            SQL
                            ,
                            'bindings' => [
                                2,
                                22,
                            ],
                        ],
                        new Field('operator name should be ignored'),
                        $factory,
                        null,
                        null,
                    ],
                    'with alias' => [
                        [
                            'query'    => <<<'SQL'
                                select * from "test_objects" where (("alias"."a" = ?) or ("alias"."b" != ?))
                            SQL
                            ,
                            'bindings' => [
                                2,
                                22,
                            ],
                        ],
                        new Field('alias', 'operator name should be ignored'),
                        $factory,
                        null,
                        null,
                    ],
                    'resolver'   => [
                        [
                            'query'    => <<<'SQL'
                                select * from "test_objects" where (("alias__a" = ?) or ("alias__b" != ?))
                            SQL
                            ,
                            'bindings' => [
                                2,
                                22,
                            ],
                        ],
                        new Field('alias', 'operator name should be ignored'),
                        $factory,
                        null,
                        static function (object $builder, Field $field): string {
                            return implode('__', $field->getPath());
                        },
                    ],
                ]),
            ),
        ]))->getData();
    }
    // </editor-fold>
}
