<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Directives;

use Exception;
use GraphQL\Type\Definition\InputObjectType;
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
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Mockery;
use Mockery\MockInterface;
use Nuwave\Lighthouse\Schema\TypeRegistry;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SortBy\Directives\Directive
 */
class DirectiveTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::manipulateArgDefinition
     *
     * @dataProvider dataProviderManipulateArgDefinition
     */
    public function testManipulateArgDefinition(string $expected, string $graphql): void {
        $this->assertGraphQLSchemaEquals(
            $this->getTestData()->file($expected),
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
                    'type' => Type::boolean(),
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

        $registry = $this->app->make(TypeRegistry::class);
        $registry->register($a);
        $registry->register($b);

        $this->assertGraphQLSchemaEquals(
            $this->getTestData()->file('~registry-expected.graphql'),
            $this->getTestData()->file('~registry.graphql'),
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
            $this->expectExceptionObject($expected);
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

        $this->assertEquals($expected, $directive->getClauses($clauses));
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
     * @return array<mixed>
     */
    public function dataProviderManipulateArgDefinition(): array {
        return [
            'full' => ['~full-expected.graphql', '~full.graphql'],
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
