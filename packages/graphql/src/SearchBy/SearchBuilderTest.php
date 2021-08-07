<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy;

use Closure;
use Exception;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\ComplexOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\LogicalOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\Client\SearchConditionEmpty;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\Client\SearchConditionTooManyOperators;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\Client\SearchConditionTooManyProperties;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\OperatorNotFound;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\Equal;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\GreaterThan;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\NotEqual;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical\AllOf;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical\AnyOf;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical\Not;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\EloquentBuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\QueryBuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\MergeDataProvider;
use Mockery;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SearchBy\SearchBuilder
 */
class SearchBuilderTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::process
     *
     * @dataProvider dataProviderProcess
     *
     * @param array<mixed> $conditions
     */
    public function testProcess(
        array|Exception $expected,
        Closure $builder,
        array $conditions = [],
        string $tableAlias = null,
    ): void {
        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $search  = new SearchBuilder([
            $this->app->make(Equal::class),
            $this->app->make(NotEqual::class),
            $this->app->make(GreaterThan::class),
            $this->app->make(AllOf::class),
            $this->app->make(AnyOf::class),
            $this->app->make(Not::class),
        ]);
        $builder = $builder($this);
        $builder = $search->process($builder, $conditions, $tableAlias);
        $actual  = [
            'sql'      => $builder->toSql(),
            'bindings' => $builder->getBindings(),
        ];

        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers ::processComparison
     *
     * @dataProvider dataProviderProcessComparison
     *
     * @param array<mixed> $conditions
     */
    public function testProcessComparison(
        array|Exception $expected,
        Closure $builder,
        string $property,
        array $conditions = [],
        string $tableAlias = null,
    ): void {
        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $search  = new SearchBuilder([
            $this->app->make(NotEqual::class),
            $this->app->make(Equal::class),
            $this->app->make(GreaterThan::class),
        ]);
        $builder = $builder($this);
        $builder = $search->processComparison($builder, $property, $conditions, $tableAlias);
        $actual  = [
            'sql'      => $builder->toSql(),
            'bindings' => $builder->getBindings(),
        ];

        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers ::processLogicalOperator
     *
     * @dataProvider dataProviderProcessLogicalOperator
     *
     * @param array<mixed> $expected
     */
    public function testProcessLogicalOperator(array $expected, Closure $builder): void {
        $conditions = [1, 2];
        $logical    = Mockery::mock(LogicalOperator::class, Operator::class);

        $logical
            ->shouldReceive('getName')
            ->once()
            ->andReturn('and');

        $search = new SearchBuilder([$logical]);

        $logical
            ->shouldReceive('apply')
            ->once()
            ->andReturnUsing(
                static function (
                    SearchBuilder $search,
                    EloquentBuilder|QueryBuilder $builder,
                    array $conditions,
                    ?string $tableAlias,
                ): EloquentBuilder|QueryBuilder {
                    return $builder->whereRaw('(1 = 1)');
                },
            );

        $builder = $builder($this);
        $builder = $search->processLogicalOperator($builder, $logical, $conditions);
        $actual  = [
            'sql'      => $builder->toSql(),
            'bindings' => $builder->getBindings(),
        ];

        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers ::processComplexOperator
     *
     * @dataProvider dataProviderProcessComplexOperator
     *
     * @param array<mixed> $expected
     */
    public function testProcessComplexOperator(array $expected, Closure $builder): void {
        $conditions = [1, 2, 4];
        $property   = 'property';
        $complex    = Mockery::mock(ComplexOperator::class);

        $complex
            ->shouldReceive('getName')
            ->once()
            ->andReturn('test');

        $search = new SearchBuilder([$complex]);

        $complex
            ->shouldReceive('apply')
            ->with($search, Mockery::any(), $property, $conditions)
            ->once()
            ->andReturnUsing(
                static function (
                    SearchBuilder $search,
                    EloquentBuilder|QueryBuilder $builder,
                ): EloquentBuilder|QueryBuilder {
                    return $builder->whereRaw('1 = 1');
                },
            );

        $builder = $builder($this);
        $builder = $search->processComplexOperator($builder, $complex, $property, $conditions);
        $actual  = [
            'sql'      => $builder->toSql(),
            'bindings' => $builder->getBindings(),
        ];

        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers ::getComplexOperator
     */
    public function testGetComplexOperator(): void {
        $complex = new class() implements ComplexOperator {
            /**
             * @inheritdoc
             */
            public function apply(
                SearchBuilder $search,
                EloquentBuilder|QueryBuilder $builder,
                string $property,
                array $conditions,
            ): EloquentBuilder|QueryBuilder {
                return $builder;
            }

            public function getName(): string {
                return 'test';
            }

            public function getDefinition(
                Manipulator $ast,
                InputValueDefinitionNode $field,
                InputObjectTypeDefinitionNode $type,
                string $name,
                bool $nullable,
            ): InputObjectTypeDefinitionNode {
                throw new Exception('Should not be used.');
            }
        };
        $search  = new SearchBuilder([$complex]);

        $this->assertSame($complex, $search->getComplexOperator([
            $complex->getName() => 'yes',
        ]));

        $this->assertNull($search->getComplexOperator([]));
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    public function dataProviderProcess(): array {
        return (new MergeDataProvider([
            'Both'     => (new CompositeDataProvider(
                new BuilderDataProvider(),
                new ArrayDataProvider([
                    'more than one property'           => [
                        new SearchConditionTooManyProperties(['a', 'b']),
                        [
                            'a' => [
                                'equal' => 2,
                            ],
                            'b' => [
                                'notEqual' => 3,
                            ],
                        ],
                        null,
                    ],
                    'valid condition with table alias' => [
                        [
                            'sql'      => 'select * from "tmp" where ('.
                                'not ((("alias"."a" != ?) and ((("alias"."a" = ?) or ("alias"."b" != ?)))))'.
                                ')',
                            'bindings' => [
                                1,
                                2,
                                3,
                            ],
                        ],
                        [
                            'not' => [
                                'allOf' => [
                                    [
                                        'a' => [
                                            'notEqual' => 1,
                                        ],
                                    ],
                                    [
                                        'anyOf' => [
                                            [
                                                'a' => [
                                                    'equal' => 2,
                                                ],
                                            ],
                                            [
                                                'b' => [
                                                    'notEqual' => 3,
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'alias',
                    ],
                ]),
            )),
            'Query'    => (new CompositeDataProvider(
                new QueryBuilderDataProvider(),
                new ArrayDataProvider([
                    'valid condition' => [
                        [
                            'sql'      => 'select * from "tmp" where ('.
                                'not ((("a" != ?) and ((("a" = ?) or ("b" != ?)))))'.
                                ')',
                            'bindings' => [
                                1,
                                2,
                                3,
                            ],
                        ],
                        [
                            'not' => [
                                'allOf' => [
                                    [
                                        'a' => [
                                            'notEqual' => 1,
                                        ],
                                    ],
                                    [
                                        'anyOf' => [
                                            [
                                                'a' => [
                                                    'equal' => 2,
                                                ],
                                            ],
                                            [
                                                'b' => [
                                                    'notEqual' => 3,
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        null,
                    ],
                ]),
            )),
            'Eloquent' => (new CompositeDataProvider(
                new EloquentBuilderDataProvider(),
                new ArrayDataProvider([
                    'valid condition' => [
                        [
                            'sql'      => 'select * from "tmp" where ('.
                                'not ((("tmp"."a" != ?) and ((("tmp"."a" = ?) or ("tmp"."b" != ?)))))'.
                                ')',
                            'bindings' => [
                                1,
                                2,
                                3,
                            ],
                        ],
                        [
                            'not' => [
                                'allOf' => [
                                    [
                                        'a' => [
                                            'notEqual' => 1,
                                        ],
                                    ],
                                    [
                                        'anyOf' => [
                                            [
                                                'a' => [
                                                    'equal' => 2,
                                                ],
                                            ],
                                            [
                                                'b' => [
                                                    'notEqual' => 3,
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        null,
                    ],
                ]),
            )),
        ]))->getData();
    }

    /**
     * @return array<mixed>
     */
    public function dataProviderProcessLogicalOperator(): array {
        return (new CompositeDataProvider(
            new BuilderDataProvider(),
            new ArrayDataProvider([
                'ok' => [
                    [
                        'sql'      => 'select * from "tmp" where ((1 = 1))',
                        'bindings' => [],
                    ],
                ],
            ]),
        ))->getData();
    }

    /**
     * @return array<mixed>
     */
    public function dataProviderProcessComplexOperator(): array {
        return (new CompositeDataProvider(
            new BuilderDataProvider(),
            new ArrayDataProvider([
                'ok' => [
                    [
                        'sql'      => 'select * from "tmp" where (1 = 1)',
                        'bindings' => [],
                    ],
                ],
            ]),
        ))->getData();
    }

    /**
     * @return array<mixed>
     */
    public function dataProviderProcessComparison(): array {
        return (new MergeDataProvider([
            'Both'     => (new CompositeDataProvider(
                new BuilderDataProvider(),
                new ArrayDataProvider([
                    'empty'                            => [
                        new SearchConditionEmpty(),
                        'property',
                        [],
                        null,
                    ],
                    'more than one condition'          => [
                        new SearchConditionTooManyOperators(['equal', 'in']),
                        'property',
                        [
                            'equal' => 'yes',
                            'in'    => [1, 2],
                        ],
                        null,
                    ],
                    'unknown operator'                 => [
                        new OperatorNotFound('unk'),
                        'property',
                        [
                            'unk' => 'yes',
                        ],
                        null,
                    ],
                    'valid condition with table alias' => [
                        [
                            'sql'      => 'select * from "tmp" where "alias"."property" = ?',
                            'bindings' => [
                                123,
                            ],
                        ],
                        'property',
                        [
                            'equal' => 123,
                        ],
                        'alias',
                    ],
                ]),
            )),
            'Query'    => (new CompositeDataProvider(
                new QueryBuilderDataProvider(),
                new ArrayDataProvider([
                    'valid condition' => [
                        [
                            'sql'      => 'select * from "tmp" where "property" = ?',
                            'bindings' => [
                                123,
                            ],
                        ],
                        'property',
                        [
                            'equal' => 123,
                        ],
                        null,
                    ],
                ]),
            )),
            'Eloquent' => (new CompositeDataProvider(
                new EloquentBuilderDataProvider(),
                new ArrayDataProvider([
                    'valid condition' => [
                        [
                            'sql'      => 'select * from "tmp" where "tmp"."property" = ?',
                            'bindings' => [
                                123,
                            ],
                        ],
                        'property',
                        [
                            'equal' => 123,
                        ],
                        null,
                    ],
                ]),
            )),
        ]))->getData();
    }
    // </editor-fold>
}
