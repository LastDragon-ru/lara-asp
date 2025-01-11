<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Directives;

use Closure;
use Exception;
use GraphQL\Language\DirectiveLocation;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\BuilderFieldResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionTooManyFields;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeDefinitionImpossibleToCreateType;
use LastDragon_ru\LaraASP\GraphQL\Builder\Field;
use LastDragon_ru\LaraASP\GraphQL\Config\Config;
use LastDragon_ru\LaraASP\GraphQL\PackageConfig;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\Ignored;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByOperatorFieldDirective;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByOperatorRandomDirective;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Direction;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Nulls;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators\Child;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Types\Clause\Clause;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Data\Models\WithTestObject;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\ScoutBuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Requirements\RequiresLaravelScout;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonMatchesFragment;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Bodies\JsonBody;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentTypes\JsonContentType;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Response;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes\Ok;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use Mockery\MockInterface;
use Nuwave\Lighthouse\Pagination\PaginationServiceProvider as LighthousePaginationServiceProvider;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Directives\RenameDirective;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Nuwave\Lighthouse\Scout\SearchDirective;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function array_merge;
use function implode;
use function is_array;
use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * @internal
 */
#[CoversClass(Directive::class)]
final class DirectiveTest extends TestCase {
    use WithTestObject;
    use MakesGraphQLRequests;

    // <editor-fold desc="Prepare">
    // =========================================================================
    /**
     * @inheritDoc
     */
    #[Override]
    protected function getPackageProviders(mixed $app): array {
        return array_merge(parent::getPackageProviders($app), [
            LighthousePaginationServiceProvider::class,
        ]);
    }
    // </editor-fold>

    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param Closure(static): void|null $prepare
     */
    #[DataProvider('dataProviderManipulateArgDefinition')]
    public function testManipulateArgDefinition(string $expected, string $graphql, ?Closure $prepare = null): void {
        if ($prepare !== null) {
            $prepare($this);
        }

        $this->useGraphQLSchema(
            self::getTestData()->file($graphql),
        );

        $this->assertGraphQLSchemaEquals(self::getTestData()->file($expected));
        $this->assertGraphQLSchemaValid();
    }

    #[RequiresLaravelScout]
    public function testManipulateArgDefinitionScoutBuilder(): void {
        $this->setConfiguration(PackageConfig::class, static function (Config $config): void {
            $config->sortBy->operators = [
                Operators::Extra => [
                    Operators::Extra,
                    SortByOperatorRandomDirective::class,
                ],
            ];
        });

        $this->app()->make(DirectiveLocator::class)
            ->setResolved('search', SearchDirective::class);

        $this->useGraphQLSchema(
            self::getTestData()->file('Scout.schema.graphql'),
        );

        $this->assertGraphQLSchemaEquals(
            self::getTestData()->file('Scout.expected.graphql'),
        );
        $this->assertGraphQLSchemaValid();
    }

    public function testManipulateArgDefinitionTypeRegistryEmpty(): void {
        $this->setConfiguration(PackageConfig::class, static function (Config $config): void {
            $config->sortBy->operators = [
                Operators::Extra => [
                    SortByOperatorFieldDirective::class,
                ],
            ];
        });

        $type = new ObjectType([
            'name'   => 'TestType',
            'fields' => [
                [
                    'name' => 'list',
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(Type::boolean()))),
                ],
            ],
        ]);

        self::expectExceptionObject(
            new TypeDefinitionImpossibleToCreateType(Clause::class, 'type TestType', new Context()),
        );

        $registry = $this->app()->make(TypeRegistry::class);
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
     * @param array{query: string, bindings: array<array-key, mixed>}|Exception $expected
     * @param Closure(static): object                                           $builderFactory
     * @param Closure(static): void|null                                        $prepare
     */
    #[DataProvider('dataProviderHandleBuilder')]
    public function testDirective(
        array|Exception $expected,
        Closure $builderFactory,
        mixed $value,
        ?Closure $prepare = null,
    ): void {
        if ($prepare !== null) {
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
                query test($input: [SortByRootTestObject!]) {
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
     * @param array{query: string, bindings: array<array-key, mixed>}|Exception $expected
     * @param Closure(static): (QueryBuilder|EloquentBuilder<EloquentModel>)    $builderFactory
     * @param Closure(static): void|null                                        $prepare
     */
    #[DataProvider('dataProviderHandleBuilder')]
    public function testHandleBuilder(
        array|Exception $expected,
        Closure $builderFactory,
        mixed $value,
        ?Closure $prepare = null,
    ): void {
        if ($prepare !== null) {
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
                value: String @rename(attribute: "renamed.field")
            }
            GRAPHQL,
        );

        $type = match (true) {
            $builder instanceof QueryBuilder => 'SortByQueryRootTest',
            default                          => 'SortByRootTest',
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
     * @param array<string, mixed>|Exception               $expected
     * @param Closure(static): ScoutBuilder<EloquentModel> $builderFactory
     * @param Closure(object, Field): string|null          $resolver
     */
    #[DataProvider('dataProviderHandleScoutBuilder')]
    #[RequiresLaravelScout]
    public function testHandleScoutBuilder(
        array|Exception $expected,
        Closure $builderFactory,
        mixed $value,
        ?Closure $resolver,
    ): void {
        $builder   = $builderFactory($this);
        $directive = $this->getExposeBuilderDirective($builder);

        $this->app()->make(DirectiveLocator::class)
            ->setResolved('search', SearchDirective::class);

        if ($resolver !== null) {
            $this->override(
                BuilderFieldResolver::class,
                static function (MockInterface $mock) use ($resolver): void {
                    $mock
                        ->shouldReceive('getField')
                        ->atLeast()
                        ->once()
                        ->andReturnUsing($resolver);
                },
            );
        }

        $this->useGraphQLSchema(
            <<<GRAPHQL
            type Query {
                test(search: String @search, input: Test @sortBy): [String!]! {$directive::getName()}
            }

            input Test {
                a: Int!
                b: String @rename(attribute: "renamed.field")
                c: Test
            }
            GRAPHQL,
        );

        $result = $this->graphQL(
            <<<'GRAPHQL'
            query test($query: [SortByScoutRootTest!]) {
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
     * @return array<string,array{string, string, Closure(static): void|null}>
     */
    public static function dataProviderManipulateArgDefinition(): array {
        return [
            'Explicit'          => [
                'Explicit.expected.graphql',
                'Explicit.schema.graphql',
                null,
            ],
            'Implicit'          => [
                'Implicit.expected.graphql',
                'Implicit.schema.graphql',
                static function (TestCase $test): void {
                    $test->app()->make(DirectiveLocator::class)
                        ->setResolved(
                            DirectiveLocator::directiveName(DirectiveTest__CustomOperatorDirective::class),
                            DirectiveTest__CustomOperatorDirective::class,
                        );
                },
            ],
            'Query'             => [
                'Query.expected.graphql',
                'Query.schema.graphql',
                null,
            ],
            'ScalarOperators'   => [
                'ScalarOperators.expected.graphql',
                'ScalarOperators.schema.graphql',
                null,
            ],
            'AllowedDirectives' => [
                'AllowedDirectives.expected.graphql',
                'AllowedDirectives.schema.graphql',
                static function (TestCase $test): void {
                    $locator   = $test->app()->make(DirectiveLocator::class);
                    $allowed   = new class () extends BaseDirective {
                        #[Override]
                        public static function definition(): string {
                            return <<<'GRAPHQL'
                                directive @allowed on INPUT_FIELD_DEFINITION | FIELD_DEFINITION
                                GRAPHQL;
                        }
                    };
                    $forbidden = new class () extends BaseDirective {
                        #[Override]
                        public static function definition(): string {
                            return <<<'GRAPHQL'
                                directive @forbidden on INPUT_FIELD_DEFINITION | FIELD_DEFINITION
                                GRAPHQL;
                        }
                    };

                    $locator->setResolved('allowed', $allowed::class);
                    $locator->setResolved('forbidden', $forbidden::class);

                    $test->setConfiguration(
                        PackageConfig::class,
                        static function (Config $config) use ($allowed): void {
                            $config->builder->allowedDirectives          = [
                                RenameDirective::class,
                                $allowed::class,
                            ];
                            $config->sortBy->operators[Operators::Extra] = [
                                SortByOperatorFieldDirective::class,
                            ];
                        },
                    );
                },
            ],
            'Ignored'           => [
                'Ignored.expected.graphql',
                'Ignored.schema.graphql',
                static function (TestCase $test): void {
                    $test->setConfiguration(PackageConfig::class, static function (Config $config): void {
                        $config->sortBy->operators[Operators::Extra] = [
                            SortByOperatorFieldDirective::class,
                        ];
                    });

                    $test->app()->make(TypeRegistry::class)->register(
                        new class([
                            'name'   => 'IgnoredType',
                            'fields' => [
                                [
                                    'name' => 'name',
                                    'type' => Type::nonNull(Type::string()),
                                ],
                            ],
                        ]) extends ObjectType implements Ignored {
                            // empty
                        },
                    );
                },
            ],
            'InterfaceUpdate'   => [
                'InterfaceUpdate.expected.graphql',
                'InterfaceUpdate.schema.graphql',
                static function (TestCase $test): void {
                    $test->setConfiguration(PackageConfig::class, static function (Config $config): void {
                        $config->sortBy->operators[Operators::Extra] = [
                            SortByOperatorFieldDirective::class,
                        ];
                    });
                },
            ],
            'TypeRegistry'      => [
                'Example.expected.graphql',
                'Example.schema.graphql',
                static function (TestCase $test): void {
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

                    $registry = $test->app()->make(TypeRegistry::class);
                    $registry->register($a);
                    $registry->register($b);
                    $registry->register($c);
                    $registry->register($d);
                    $registry->register($i);
                },
            ],
            'Example'           => [
                'Example.expected.graphql',
                'Example.schema.graphql',
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
                'empty'                       => [
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
                'empty operators'             => [
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
                'too many fields (operators)' => [
                    new ConditionTooManyFields(['nullsFirst', 'field']),
                    [
                        [
                            'field'      => [
                                'id' => 'Asc',
                            ],
                            'nullsFirst' => [
                                'id' => 'Desc',
                            ],
                        ],
                    ],
                    null,
                ],
                'too many fields (fields)'    => [
                    new ConditionTooManyFields(['id', 'value']),
                    [
                        [
                            'field' => [
                                'id'    => 'Asc',
                                'value' => 'Desc',
                            ],
                        ],
                    ],
                    null,
                ],
                'null'                        => [
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
                'valid condition'             => [
                    [
                        'query'    => <<<'SQL'
                            select
                                *
                            from
                                "test_objects"
                            order by
                                "id" asc,
                                "renamed"."field" desc,
                                RANDOM()
                        SQL
                        ,
                        'bindings' => [],
                    ],
                    [
                        [
                            'field' => [
                                'id' => 'Asc',
                            ],
                        ],
                        [
                            'field' => [
                                'value' => 'Desc',
                            ],
                        ],
                        [
                            'random' => 'Yes',
                        ],
                    ],
                    static function (TestCase $test): void {
                        $test->setConfiguration(PackageConfig::class, static function (Config $config): void {
                            $config->sortBy->operators = [
                                Operators::Extra => [
                                    SortByOperatorFieldDirective::class,
                                    SortByOperatorRandomDirective::class,
                                ],
                            ];
                        });
                    },
                ],
                'nulls ordering'              => [
                    [
                        'query'    => <<<'SQL'
                            select
                                *
                            from
                                "test_objects"
                            order by
                                "id" ASC NULLS LAST,
                                "renamed"."field" DESC NULLS FIRST
                        SQL
                        ,
                        'bindings' => [],
                    ],
                    [
                        [
                            'field' => [
                                'id' => 'Asc',
                            ],
                        ],
                        [
                            'field' => [
                                'value' => 'Desc',
                            ],
                        ],
                    ],
                    static function (TestCase $test): void {
                        $test->setConfiguration(PackageConfig::class, static function (Config $config): void {
                            $config->sortBy->nulls = [
                                Direction::Asc->value  => Nulls::Last,
                                Direction::Desc->value => Nulls::First,
                            ];
                        });
                    },
                ],
                'nullsFirst'                  => [
                    [
                        'query'    => <<<'SQL'
                            select
                                *
                            from
                                "test_objects"
                            order by
                                "id" DESC NULLS FIRST,
                                "renamed"."field" asc
                        SQL
                        ,
                        'bindings' => [],
                    ],
                    [
                        [
                            'nullsFirst' => [
                                'id' => 'Desc',
                            ],
                        ],
                        [
                            'field' => [
                                'value' => 'Asc',
                            ],
                        ],
                    ],
                    null,
                ],
                'nullsLast'                   => [
                    [
                        'query'    => <<<'SQL'
                            select
                                *
                            from
                                "test_objects"
                            order by
                                "id" ASC NULLS LAST,
                                "renamed"."field" desc
                        SQL
                        ,
                        'bindings' => [],
                    ],
                    [
                        [
                            'nullsLast' => [
                                'id' => 'Asc',
                            ],
                        ],
                        [
                            'field' => [
                                'value' => 'Desc',
                            ],
                        ],
                    ],
                    null,
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
                'empty'                    => [
                    [
                        // empty
                    ],
                    [
                        // empty
                    ],
                    null,
                    null,
                ],
                'empty operators'          => [
                    [
                        // empty
                    ],
                    [
                        [
                            // empty
                        ],
                    ],
                    null,
                    null,
                ],
                'too many fields (fields)' => [
                    new ConditionTooManyFields(['a', 'b']),
                    [
                        [
                            'field' => [
                                'a' => 'Asc',
                                'b' => 'Desc',
                            ],
                        ],
                    ],
                    null,
                    null,
                ],
                'null'                     => [
                    [
                        // empty
                    ],
                    null,
                    null,
                    null,
                ],
                'default field resolver'   => [
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
                                'column'    => 'renamed.field',
                                'direction' => 'desc',
                            ],
                        ],
                    ],
                    [
                        [
                            'field' => [
                                'a' => 'Asc',
                            ],
                        ],
                        [
                            'field' => [
                                'c' => [
                                    'a' => 'Desc',
                                ],
                            ],
                        ],
                        [
                            'field' => [
                                'b' => 'Desc',
                            ],
                        ],
                    ],
                    null,
                    null,
                ],
                'resolver'                 => [
                    [
                        'orders' => [
                            [
                                'column'    => 'a',
                                'direction' => 'asc',
                            ],
                            [
                                'column'    => 'c__a',
                                'direction' => 'desc',
                            ],
                            [
                                'column'    => 'renamed.field',
                                'direction' => 'desc',
                            ],
                        ],
                    ],
                    [
                        [
                            'field' => [
                                'a' => 'Asc',
                            ],
                        ],
                        [
                            'field' => [
                                'c' => [
                                    'a' => 'Desc',
                                ],
                            ],
                        ],
                        [
                            'field' => [
                                'b' => 'Desc',
                            ],
                        ],
                    ],
                    static function (object $builder, Field $field): string {
                        return implode('__', $field->getPath());
                    },
                    null,
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
class DirectiveTest__Resolver {
    public function __invoke(): mixed {
        throw new Exception('Should not be called.');
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class DirectiveTest__QueryBuilderResolver {
    public function __invoke(): QueryBuilder {
        throw new Exception('Should not be called.');
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class DirectiveTest__CustomOperatorDirective extends Child {
    /**
     * @inheritDoc
     */
    #[Override]
    protected static function locations(): array {
        return array_merge(parent::locations(), [DirectiveLocation::FIELD_DEFINITION]);
    }
}
