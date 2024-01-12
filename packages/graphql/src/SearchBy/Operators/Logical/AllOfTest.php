<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scout\FieldResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\EloquentBuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\QueryBuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\ScoutBuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\OperatorTests;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\MergeDataProvider;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function implode;

/**
 * @internal
 *
 * @phpstan-import-type BuilderFactory from BuilderDataProvider
 */
#[CoversClass(AllOf::class)]
final class AllOfTest extends TestCase {
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
     */
    public function testCall(
        array $expected,
        Closure $builderFactory,
        Property $property,
        Closure $argumentFactory,
        ?Closure $contextFactory,
    ): void {
        $this->testOperator(Directive::class, $expected, $builderFactory, $property, $argumentFactory, $contextFactory);
    }

    /**
     * @dataProvider dataProviderCallScout
     *
     * @param array<string, mixed>          $expected
     * @param Closure(static): ScoutBuilder $builderFactory
     * @param Closure(static): Argument     $argumentFactory
     * @param Closure(static): Context|null $contextFactory
     * @param Closure():FieldResolver|null  $resolver
     */
    public function testCallScout(
        array $expected,
        Closure $builderFactory,
        Property $property,
        Closure $argumentFactory,
        ?Closure $contextFactory,
        Closure $resolver = null,
    ): void {
        if ($resolver) {
            $this->override(FieldResolver::class, $resolver);
        }

        $this->testOperator(Directive::class, $expected, $builderFactory, $property, $argumentFactory, $contextFactory);
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
                            'query'    => 'select * from "test_objects" where (("a" = ?) and ("b" != ?))',
                            'bindings' => [
                                2,
                                22,
                            ],
                        ],
                        new Property('operator name should be ignored'),
                        $factory,
                        null,
                    ],
                    'with alias' => [
                        [
                            'query'    => <<<'SQL'
                                select * from "test_objects" where (("alias"."a" = ?) and ("alias"."b" != ?))
                            SQL
                            ,
                            'bindings' => [
                                2,
                                22,
                            ],
                        ],
                        new Property('alias', 'operator name should be ignored'),
                        $factory,
                        null,
                    ],
                ]),
            ),
            'Eloquent' => new CompositeDataProvider(
                new EloquentBuilderDataProvider(),
                new ArrayDataProvider([
                    'property'   => [
                        [
                            'query'    => <<<'SQL'
                                select *
                                from "test_objects"
                                where (("test_objects"."a" = ?) and ("test_objects"."b" != ?))
                            SQL
                            ,
                            'bindings' => [
                                2,
                                22,
                            ],
                        ],
                        new Property('operator name should be ignored'),
                        $factory,
                        null,
                    ],
                    'with alias' => [
                        [
                            'query'    => <<<'SQL'
                                select * from "test_objects" where (("alias"."a" = ?) and ("alias"."b" != ?))
                            SQL
                            ,
                            'bindings' => [
                                2,
                                22,
                            ],
                        ],
                        new Property('alias', 'operator name should be ignored'),
                        $factory,
                        null,
                    ],
                ]),
            ),
        ]))->getData();
    }

    /**
     * @return array<array-key, mixed>
     */
    public static function dataProviderCallScout(): array {
        $factory = static function (self $test): Argument {
            return $test->getGraphQLArgument(
                '[TestInput!]',
                [
                    ['a' => ['equal' => 'aaa']],
                    ['b' => ['equal' => 'bbb']],
                    ['b' => ['in' => [1, 2, 3]]],
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

                        in: [Int!]
                        @searchByOperatorIn
                    }

                    type Query {
                        test(input: TestInput): Int @all
                    }
                GRAPHQL,
            );
        };

        return (new CompositeDataProvider(
            new ScoutBuilderDataProvider(),
            new ArrayDataProvider([
                'property'               => [
                    [
                        'wheres'   => [
                            'path.to.property.a' => 'aaa',
                            'path.to.property.b' => 'bbb',
                        ],
                        'whereIns' => [
                            'path.to.property.b' => [1, 2, 3],
                        ],
                    ],
                    new Property('path', 'to', 'property', 'operator name should be ignored'),
                    $factory,
                    null,
                    null,
                ],
                'property with resolver' => [
                    [
                        'wheres'   => [
                            'properties/path/to/property/a' => 'aaa',
                            'properties/path/to/property/b' => 'bbb',
                        ],
                        'whereIns' => [
                            'properties/path/to/property/b' => [1, 2, 3],
                        ],
                    ],
                    new Property('path', 'to', 'property', 'operator name should be ignored'),
                    $factory,
                    null,
                    static function (): FieldResolver {
                        return new class() implements FieldResolver {
                            /**
                             * @inheritDoc
                             */
                            #[Override]
                            public function getField(Model $model, Property $property): string {
                                return 'properties/'.implode('/', $property->getPath());
                            }
                        };
                    },
                ],
            ]),
        ))->getData();
    }
    // </editor-fold>
}
