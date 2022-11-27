<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical;

use Closure;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\EloquentBuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\QueryBuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\MergeDataProvider;
use Nuwave\Lighthouse\Execution\Arguments\Argument;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical\AnyOf
 *
 * @phpstan-import-type BuilderFactory from BuilderDataProvider
 */
class AnyOfTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::call
     *
     * @dataProvider dataProviderCall
     *
     * @param array{query: string, bindings: array<mixed>} $expected
     * @param BuilderFactory                               $builderFactory
     * @param Closure(static): Argument                    $argumentFactory
     */
    public function testCall(
        array $expected,
        Closure $builderFactory,
        Property $property,
        Closure $argumentFactory,
    ): void {
        $operator = $this->app->make(AnyOf::class);
        $property = $property->getChild('operator name should be ignored');
        $argument = $argumentFactory($this);
        $search   = $this->app->make(Directive::class);
        $builder  = $builderFactory($this);
        $builder  = $operator->call($search, $builder, $property, $argument);

        self::assertDatabaseQueryEquals($expected, $builder);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    public function dataProviderCall(): array {
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
                        @searchByOperatorProperty

                        b: TestOperators
                        @searchByOperatorProperty
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
                    'property'   => [
                        [
                            'query'    => 'select * from "tmp" where (("a" = ?) or ("b" != ?))',
                            'bindings' => [
                                2,
                                22,
                            ],
                        ],
                        new Property(),
                        $factory,
                    ],
                    'with alias' => [
                        [
                            'query'    => 'select * from "tmp" where (("alias"."a" = ?) or ("alias"."b" != ?))',
                            'bindings' => [
                                2,
                                22,
                            ],
                        ],
                        new Property('alias'),
                        $factory,
                    ],
                ]),
            ),
            'Eloquent' => new CompositeDataProvider(
                new EloquentBuilderDataProvider(),
                new ArrayDataProvider([
                    'property'   => [
                        [
                            'query'    => 'select * from "tmp" where (("tmp"."a" = ?) or ("tmp"."b" != ?))',
                            'bindings' => [
                                2,
                                22,
                            ],
                        ],
                        new Property(),
                        $factory,
                    ],
                    'with alias' => [
                        [
                            'query'    => 'select * from "tmp" where (("alias"."a" = ?) or ("alias"."b" != ?))',
                            'bindings' => [
                                2,
                                22,
                            ],
                        ],
                        new Property('alias'),
                        $factory,
                    ],
                ]),
            ),
        ]))->getData();
    }
    // </editor-fold>
}
