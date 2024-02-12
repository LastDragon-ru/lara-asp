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
#[CoversClass(Not::class)]
final class NotTest extends TestCase {
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
                'TestInput',
                [
                    'a' => [
                        'equal' => 2,
                    ],
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
                            'query'    => 'select * from "test_objects" where (not ("a" = ?))',
                            'bindings' => [
                                2,
                            ],
                        ],
                        new Field('operator name should be ignored'),
                        $factory,
                        null,
                        null,
                    ],
                    'with alias' => [
                        [
                            'query'    => 'select * from "test_objects" where (not ("alias"."a" = ?))',
                            'bindings' => [
                                2,
                            ],
                        ],
                        new Field('alias', 'operator name should be ignored'),
                        $factory,
                        null,
                        null,
                    ],
                    'resolver'   => [
                        [
                            'query'    => 'select * from "test_objects" where (not ("alias__a" = ?))',
                            'bindings' => [
                                2,
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
                            'query'    => 'select * from "test_objects" where (not ("test_objects"."a" = ?))',
                            'bindings' => [
                                2,
                            ],
                        ],
                        new Field('operator name should be ignored'),
                        $factory,
                        null,
                        null,
                    ],
                    'with alias' => [
                        [
                            'query'    => 'select * from "test_objects" where (not ("alias"."a" = ?))',
                            'bindings' => [
                                2,
                            ],
                        ],
                        new Field('alias', 'operator name should be ignored'),
                        $factory,
                        null,
                        null,
                    ],
                    'resolver'   => [
                        [
                            'query'    => 'select * from "test_objects" where (not ("alias__a" = ?))',
                            'bindings' => [
                                2,
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
