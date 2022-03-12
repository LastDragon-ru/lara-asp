<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Directives;

use Closure;
use Exception;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Clause;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Eloquent\Builder as SortByEloquentBuilder;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Query\Builder as SortByQueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Scout\Builder as SortByScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\Client\SortClauseEmpty;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\Client\SortClauseTooManyProperties;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\FailedToCreateSortClause;
use LastDragon_ru\LaraASP\GraphQL\Testing\GraphQLExpectedSchema;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Mockery;
use Mockery\MockInterface;
use Nuwave\Lighthouse\Pagination\PaginationServiceProvider;
use Nuwave\Lighthouse\Schema\TypeRegistry;

use function array_merge;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SortBy\Directives\Directive
 */
class DirectiveTest extends TestCase {
    // <editor-fold desc="Prepare">
    // =========================================================================
    /**
     * @inheritDoc
     */
    protected function getPackageProviders(mixed $app): array {
        return array_merge(parent::getPackageProviders($app), [
            PaginationServiceProvider::class,
        ]);
    }
    // </editor-fold>

    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::manipulateArgDefinition
     *
     * @dataProvider dataProviderManipulateArgDefinition
     *
     * @param Closure(self): GraphQLExpectedSchema $expected
     */
    public function testManipulateArgDefinition(Closure $expected, string $graphql): void {
        self::assertGraphQLSchemaEquals(
            $expected($this),
            $this->getTestData()->file($graphql),
        );
    }

    /**
     * @covers ::manipulateArgDefinition
     */
    public function testManipulateArgDefinitionTypeRegistry(): void {
        $a = new InputObjectType([
            'name'   => 'A',
            'fields' => [
                [
                    'name' => 'name',
                    'type' => Type::string(),
                ],
                [
                    'name' => 'flag',
                    'type' => Type::nonNull(Type::boolean()),
                ],
            ],
        ]);
        $b = new InputObjectType([
            'name'   => 'B',
            'fields' => [
                [
                    'name' => 'name',
                    'type' => Type::string(),
                ],
                [
                    'name' => 'child',
                    'type' => $a,
                ],
            ],
        ]);
        $c = new ObjectType([
            'name'   => 'C',
            'fields' => [
                [
                    'name' => 'name',
                    'type' => Type::string(),
                ],
                [
                    'name' => 'flag',
                    'type' => Type::nonNull(Type::boolean()),
                ],
                [
                    'name' => 'list',
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(Type::boolean()))),
                ],
            ],
        ]);
        $d = new ObjectType([
            'name'   => 'D',
            'fields' => [
                [
                    'name' => 'child',
                    'type' => Type::nonNull($c),
                ],
            ],
        ]);

        $registry = $this->app->make(TypeRegistry::class);
        $registry->register($a);
        $registry->register($b);
        $registry->register($c);
        $registry->register($d);

        self::assertGraphQLSchemaEquals(
            $this->getTestData()->file('~registry-expected.graphql'),
            $this->getTestData()->file('~registry.graphql'),
        );
    }

    /**
     * @covers ::manipulateArgDefinition
     */
    public function testManipulateArgDefinitionTypeRegistryEmpty(): void {
        $type = new ObjectType([
            'name'   => 'TestType',
            'fields' => [
                [
                    'name' => 'list',
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(Type::boolean()))),
                ],
            ],
        ]);

        self::expectExceptionObject(new FailedToCreateSortClause('type TestType'));

        $registry = $this->app->make(TypeRegistry::class);
        $registry->register($type);

        $this->getGraphQLSchema(
        /** @lang GraphQL */
            <<<'GRAPHQL'
            type Query {
              test(order: _ @sortBy): TestType! @all
            }
            GRAPHQL,
        );
    }

    /**
     * @covers ::getClauses
     *
     * @dataProvider dataProviderGetClauses
     *
     * @param Exception|array<array{array<string>,?string}> $expected
     * @param array<mixed>                                  $clauses
     */
    public function testGetClauses(Exception|array $expected, array $clauses): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $directive = new class() extends Directive {
            /** @noinspection PhpMissingParentConstructorInspection */
            public function __construct() {
                // empty
            }

            /**
             * @inheritDoc
             */
            public function getClauses(array $clauses): array {
                return parent::getClauses($clauses);
            }
        };

        self::assertEquals($expected, $directive->getClauses($clauses));
    }

    /**
     * @covers ::handleBuilder
     */
    public function testHandleBuilderQuery(): void {
        $directive = $this->app->make(Directive::class);
        $builder   = Mockery::mock(QueryBuilder::class);
        $clauses   = [
            new Clause(['a'], null),
        ];

        $this->override(
            SortByQueryBuilder::class,
            static function (MockInterface $mock) use ($builder, $clauses): void {
                $mock
                    ->shouldReceive('handle')
                    ->with($builder, $clauses)
                    ->once()
                    ->andReturn($builder);
            },
        );

        $directive->handleBuilder($builder, [
            ['a' => null],
        ]);
    }

    /**
     * @covers ::handleBuilder
     */
    public function testHandleBuilderEloquent(): void {
        $directive = $this->app->make(Directive::class);
        $builder   = Mockery::mock(EloquentBuilder::class);
        $clauses   = [
            new Clause(['a'], null),
        ];

        $this->override(
            SortByEloquentBuilder::class,
            static function (MockInterface $mock) use ($builder, $clauses): void {
                $mock
                    ->shouldReceive('handle')
                    ->with($builder, $clauses)
                    ->once()
                    ->andReturn($builder);
            },
        );

        $directive->handleBuilder($builder, [
            ['a' => null],
        ]);
    }

    /**
     * @covers ::handleScoutBuilder
     */
    public function testHandleScoutBuilder(): void {
        $directive = $this->app->make(Directive::class);
        $builder   = Mockery::mock(ScoutBuilder::class);
        $clauses   = [
            new Clause(['a'], null),
        ];

        $this->override(
            SortByScoutBuilder::class,
            static function (MockInterface $mock) use ($builder, $clauses): void {
                $mock
                    ->shouldReceive('handle')
                    ->with($builder, $clauses)
                    ->once()
                    ->andReturn($builder);
            },
        );

        $directive->handleScoutBuilder($builder, [
            ['a' => null],
        ]);
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<string,array{Closure(self): GraphQLExpectedSchema, string}>
     */
    public function dataProviderManipulateArgDefinition(): array {
        return [
            'full'        => [
                static function (self $test): GraphQLExpectedSchema {
                    return (new GraphQLExpectedSchema(
                        $test->getTestData()->file('~full-expected.graphql'),
                    ))
                        ->setUnusedTypes([
                            'Properties',
                            'Nested',
                            'Value',
                            'PaginatorInfo',
                            'SimplePaginatorInfo',
                            'PageInfo',
                            'String',
                            'Float',
                            'Int',
                            'Boolean',
                        ]);
                },
                '~full.graphql',
            ],
            'placeholder' => [
                static function (self $test): GraphQLExpectedSchema {
                    return (new GraphQLExpectedSchema(
                        $test->getTestData()->file('~placeholder-expected.graphql'),
                    ))
                        ->setUnusedTypes([
                            'PaginateType',
                            'Float',
                        ]);
                },
                '~placeholder.graphql',
            ],
        ];
    }

    /**
     * @return array<string,array{Exception|array<array{array<string>,?string}>|array<mixed>}>
     */
    public function dataProviderGetClauses(): array {
        return [
            'no conditions'        => [
                [],
                [],
            ],
            'empty'                => [
                new SortClauseEmpty(0, []),
                [
                    [],
                ],
            ],
            'empty nested'         => [
                new SortClauseEmpty(0, ['a' => []]),
                [
                    [
                        'a' => [],
                    ],
                ],
            ],
            'empty nested nested'  => [
                new SortClauseEmpty(0, ['a' => ['b' => []]]),
                [
                    [
                        'a' => ['b' => []],
                    ],
                ],
            ],
            'more than one'        => [
                new SortClauseTooManyProperties(1, [
                    [
                        'a' => 'desc',
                        'b' => 'desc',
                    ],
                ]),
                [
                    [
                        'a' => 'desc',
                    ],
                    [
                        'a' => 'desc',
                        'b' => 'desc',
                    ],
                ],
            ],
            'more than one nested' => [
                new SortClauseTooManyProperties(1, [
                    'a' => [
                        'a' => 'desc',
                        'b' => 'desc',
                    ],
                ]),
                [
                    [
                        'a' => 'desc',
                    ],
                    [
                        'a' => [
                            'a' => 'desc',
                            'b' => 'desc',
                        ],
                    ],
                ],
            ],
            'clause'               => [
                [
                    new Clause(['a'], null),
                    new Clause(['a'], 'desc'),
                    new Clause(['b', 'c'], 'asc'),
                    new Clause(['b', 'd', 'e'], 'desc'),
                ],
                [
                    [
                        'a' => null,
                    ],
                    [
                        'a' => 'desc',
                    ],
                    [
                        'b' => [
                            'c' => 'asc',
                        ],
                    ],
                    [
                        'b' => [
                            'd' => [
                                'e' => 'desc',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
    // </editor-fold>
}
