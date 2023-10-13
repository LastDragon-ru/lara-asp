<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Directives;

use Closure;
use Exception;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scout\FieldResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionTooManyProperties;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeDefinitionImpossibleToCreateType;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\Package;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\Ignored;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByOperatorRandomDirective;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Types\Clause;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Data\Models\WithTestObject;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\ScoutBuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\GraphQLExpected;
use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonMatchesFragment;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Bodies\JsonBody;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentTypes\JsonContentType;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Response;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes\Ok;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Nuwave\Lighthouse\Scout\SearchDirective;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use PHPUnit\Framework\Attributes\CoversClass;

use function config;
use function implode;
use function is_array;
use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * @internal
 */
#[CoversClass(Directive::class)]
class DirectiveTest extends TestCase {
    use WithTestObject;
    use MakesGraphQLRequests;

    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderManipulateArgDefinition
     *
     * @param Closure(static): GraphQLExpected $expected
     * @param Closure(static): void|null       $prepare
     */
    public function testManipulateArgDefinition(Closure $expected, string $graphql, ?Closure $prepare = null): void {
        $directives = $this->app->make(DirectiveLocator::class);

        $directives->setResolved('search', SearchDirective::class);

        if ($prepare) {
            $prepare($this);
        }

        $this->useGraphQLSchema(
            self::getTestData()->file($graphql),
        );

        self::assertGraphQLSchemaEquals(
            $expected($this),
        );
    }

    public function testManipulateArgDefinitionTypeRegistry(): void {
        $i = new class([
            'name'   => 'I',
            'fields' => [
                [
                    'name' => 'name',
                    'type' => Type::string(),
                ],
            ],
        ]) extends InputObjectType implements Ignored {
            // empty
        };
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
                [
                    'name' => 'ignored',
                    'type' => Type::nonNull($i),
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
        $registry->register($i);

        $this->useGraphQLSchema(
            self::getTestData()->file('~registry.graphql'),
        );

        self::assertGraphQLSchemaEquals(
            self::getTestData()->file('~registry-expected.graphql'),
        );
    }

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

        self::expectExceptionObject(new TypeDefinitionImpossibleToCreateType(Clause::class, 'type TestType'));

        $registry = $this->app->make(TypeRegistry::class);
        $registry->register($type);

        $this->useGraphQLSchema(
            <<<'GRAPHQL'
            type Query {
              test(order: _ @sortBy): TestType! @all
            }
            GRAPHQL,
        );
    }

    /**
     * @dataProvider dataProviderHandleBuilder
     *
     * @param array{query: string, bindings: array<array-key, mixed>}|Exception $expected
     * @param Closure(static): object                                           $builderFactory
     * @param Closure(static): void|null                                        $prepare
     */
    public function testDirective(
        array|Exception $expected,
        Closure $builderFactory,
        mixed $value,
        ?Closure $prepare = null,
    ): void {
        if ($prepare) {
            $prepare($this);
        }

        $path = is_array($expected) ? 'data.test' : 'errors.0.message';
        $body = is_array($expected) ? [] : json_encode($expected->getMessage(), JSON_THROW_ON_ERROR);

        $this
            ->useGraphQLSchema(
                <<<'GRAPHQL'
                type Query {
                    test(input: _ @sortBy): [TestObject!]!
                    @all
                }

                type TestObject {
                    id: ID!
                    value: String
                }
                GRAPHQL,
            )
            ->graphQL(
                <<<'GRAPHQL'
                query test($input: [SortByClauseTestObject!]) {
                    test(input: $input) {
                        id
                    }
                }
                GRAPHQL,
                [
                    'input' => $value,
                ],
            )
            ->assertThat(
                new Response(
                    new Ok(),
                    new JsonContentType(),
                    new JsonBody(
                        new JsonMatchesFragment($path, $body),
                    ),
                ),
            );
    }

    /**
     * @dataProvider dataProviderHandleBuilder
     *
     * @param array{query: string, bindings: array<array-key, mixed>}|Exception $expected
     * @param Closure(static): (QueryBuilder|EloquentBuilder<EloquentModel>)    $builderFactory
     * @param Closure(static): void|null                                        $prepare
     */
    public function testHandleBuilder(
        array|Exception $expected,
        Closure $builderFactory,
        mixed $value,
        ?Closure $prepare = null,
    ): void {
        if ($prepare) {
            $prepare($this);
        }

        $builder   = $builderFactory($this);
        $directive = $this->getExposeBuilderDirective($builder);

        $this->useGraphQLSchema(
            <<<GRAPHQL
            type Query {
                test(input: Test @sortBy): [String!]! {$directive::getName()}
            }

            input Test {
                id: Int!
                value: String @rename(attribute: "renamed")
            }
            GRAPHQL,
        );

        $type = match (true) {
            $builder instanceof QueryBuilder => 'SortByQueryClauseTest',
            default                          => 'SortByClauseTest',
        };
        $result = $this->graphQL(
            <<<GRAPHQL
            query test(\$query: [{$type}!]) {
                test(input: \$query)
            }
            GRAPHQL,
            [
                'query' => $value,
            ],
        );

        if (is_array($expected)) {
            self::assertInstanceOf($builder::class, $directive::$result);
            self::assertDatabaseQueryEquals($expected, $directive::$result);
        } else {
            $result->assertJsonFragment([
                'message' => $expected->getMessage(),
            ]);
        }
    }

    /**
     * @dataProvider dataProviderHandleScoutBuilder
     *
     * @param array<string, mixed>|Exception $expected
     * @param Closure(static): ScoutBuilder  $builderFactory
     * @param Closure():FieldResolver|null   $fieldResolver
     */
    public function testHandleScoutBuilder(
        array|Exception $expected,
        Closure $builderFactory,
        mixed $value,
        Closure $fieldResolver = null,
    ): void {
        $builder   = $builderFactory($this);
        $directive = $this->getExposeBuilderDirective($builder);

        $this->app->make(DirectiveLocator::class)
            ->setResolved('search', SearchDirective::class);

        if ($fieldResolver) {
            $this->override(FieldResolver::class, $fieldResolver);
        }

        $this->useGraphQLSchema(
            <<<GRAPHQL
            type Query {
                test(search: String @search, input: Test @sortBy): [String!]! {$directive::getName()}
            }

            input Test {
                a: Int!
                b: String @rename(attribute: "renamed")
                c: Test
            }
            GRAPHQL,
        );

        $result = $this->graphQL(
            <<<'GRAPHQL'
            query test($query: [SortByScoutClauseTest!]) {
                test(search: "*", input: $query)
            }
            GRAPHQL,
            [
                'query' => $value,
            ],
        );

        if (is_array($expected)) {
            self::assertInstanceOf($builder::class, $directive::$result);
            self::assertScoutQueryEquals($expected, $directive::$result);
        } else {
            $result->assertJsonFragment([
                'message' => $expected->getMessage(),
            ]);
        }
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<string,array{Closure(self): GraphQLExpected, string}>
     */
    public static function dataProviderManipulateArgDefinition(): array {
        return [
            'full'    => [
                static function (self $test): GraphQLExpected {
                    return (new GraphQLExpected(
                        $test::getTestData()->file('~full-expected.graphql'),
                    ));
                },
                '~full.graphql',
                static function (): void {
                    $package = Package::Name;

                    config([
                        "{$package}.sort_by.operators" => [
                            Operators::Extra => [
                                SortByOperatorRandomDirective::class,
                            ],
                        ],
                    ]);
                },
            ],
            'example' => [
                static function (self $test): GraphQLExpected {
                    return (new GraphQLExpected(
                        $test::getTestData()->file('~example-expected.graphql'),
                    ));
                },
                '~example.graphql',
                null,
            ],
        ];
    }

    /**
     * @return array<array-key, mixed>
     */
    public static function dataProviderHandleBuilder(): array {
        return (new CompositeDataProvider(
            new BuilderDataProvider(),
            new ArrayDataProvider([
                'empty'               => [
                    [
                        'query'    => <<<'SQL'
                            select
                                *
                            from
                                "test_objects"
                        SQL
                        ,
                        'bindings' => [],
                    ],
                    [
                        // empty
                    ],
                    null,
                ],
                'empty operators'     => [
                    [
                        'query'    => <<<'SQL'
                            select
                                *
                            from
                                "test_objects"
                        SQL
                        ,
                        'bindings' => [],
                    ],
                    [
                        [
                            // empty
                        ],
                    ],
                    null,
                ],
                'too many properties' => [
                    new ConditionTooManyProperties(['id', 'value']),
                    [
                        [
                            'id'    => 'asc',
                            'value' => 'desc',
                        ],
                    ],
                    null,
                ],
                'null'                => [
                    [
                        'query'    => <<<'SQL'
                            select
                                *
                            from
                                "test_objects"
                        SQL
                        ,
                        'bindings' => [],
                    ],
                    null,
                    null,
                ],
                'valid condition'     => [
                    [
                        'query'    => <<<'SQL'
                            select
                                *
                            from
                                "test_objects"
                            order by
                                "id" asc,
                                "renamed" desc,
                                RANDOM()
                        SQL
                        ,
                        'bindings' => [],
                    ],
                    [
                        [
                            'id' => 'asc',
                        ],
                        [
                            'value' => 'desc',
                        ],
                        [
                            'random' => 'yes',
                        ],
                    ],
                    static function (TestCase $test): void {
                        $package = Package::Name;

                        config([
                            "{$package}.sort_by.operators" => [
                                Operators::Extra => [
                                    SortByOperatorRandomDirective::class,
                                ],
                            ],
                        ]);
                    },
                ],
            ]),
        ))->getData();
    }

    /**
     * @return array<array-key, mixed>
     */
    public static function dataProviderHandleScoutBuilder(): array {
        return (new CompositeDataProvider(
            new ScoutBuilderDataProvider(),
            new ArrayDataProvider([
                'empty'                  => [
                    [
                        // empty
                    ],
                    [
                        // empty
                    ],
                    null,
                ],
                'empty operators'        => [
                    [
                        // empty
                    ],
                    [
                        [
                            // empty
                        ],
                    ],
                    null,
                ],
                'too many properties'    => [
                    new ConditionTooManyProperties(['a', 'b']),
                    [
                        [
                            'a' => 'asc',
                            'b' => 'desc',
                        ],
                    ],
                    null,
                ],
                'null'                   => [
                    [
                        // empty
                    ],
                    null,
                    null,
                ],
                'default field resolver' => [
                    [
                        'orders' => [
                            [
                                'column'    => 'a',
                                'direction' => 'asc',
                            ],
                            [
                                'column'    => 'c.a',
                                'direction' => 'desc',
                            ],
                            [
                                'column'    => 'renamed',
                                'direction' => 'desc',
                            ],
                        ],
                    ],
                    [
                        [
                            'a' => 'asc',
                        ],
                        [
                            'c' => [
                                'a' => 'desc',
                            ],
                        ],
                        [
                            'b' => 'desc',
                        ],
                    ],
                    null,
                ],
                'custom field resolver'  => [
                    [
                        'orders' => [
                            [
                                'column'    => 'properties/a',
                                'direction' => 'asc',
                            ],
                            [
                                'column'    => 'properties/c/a',
                                'direction' => 'desc',
                            ],
                            [
                                'column'    => 'properties/renamed',
                                'direction' => 'desc',
                            ],
                        ],
                    ],
                    [
                        [
                            'a' => 'asc',
                        ],
                        [
                            'c' => [
                                'a' => 'desc',
                            ],
                        ],
                        [
                            'b' => 'desc',
                        ],
                    ],
                    static function (): FieldResolver {
                        return new class() implements FieldResolver {
                            /**
                             * @inheritDoc
                             */
                            public function getField(
                                EloquentModel $model,
                                Property $property,
                            ): string {
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

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class DirectiveTest__QueryBuilderResolver {
    public function __invoke(): QueryBuilder {
        throw new Exception('should not be called.');
    }
}
