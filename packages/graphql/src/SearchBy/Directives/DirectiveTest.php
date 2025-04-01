<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives;

use Closure;
use Exception;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Str;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\BuilderFieldResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionEmpty;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionTooManyFields;
use LastDragon_ru\LaraASP\GraphQL\Builder\Field;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Config\Config;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\TypeDefinitionUnknown;
use LastDragon_ru\LaraASP\GraphQL\PackageConfig;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Ignored;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorBetweenDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorEqualDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorFieldDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Operator;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Data\Models\WithTestObject;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\EloquentBuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\QueryBuilderDataProvider;
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
use LastDragon_ru\LaraASP\Testing\Providers\MergeDataProvider;
use Mockery\MockInterface;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
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
            $config->searchBy->operators['Date'] = [
                SearchByOperatorEqualDirective::class,
            ];
        });

        $this->app()->make(DirectiveLocator::class)
            ->setResolved('search', SearchDirective::class);

        $this->useGraphQLSchema(
            self::getTestData()->file('Scout.schema.graphql'),
        );

        $this->assertGraphQLSchemaEquals(
            static::getTestData()->file('Scout.expected.graphql'),
        );
        $this->assertGraphQLSchemaValid();
    }

    public function testManipulateArgDefinitionUnknownType(): void {
        self::expectExceptionObject(new TypeDefinitionUnknown('UnknownType'));

        $this->useGraphQLSchema(
            <<<'GRAPHQL'
            type Query {
              test(where: Properties @searchBy): ID! @all
            }

            input Properties {
              value: UnknownType
            }
            GRAPHQL,
        );
    }

    /**
     * @param array{query: string, bindings: array<array-key, mixed>}|Exception $expected
     * @param Closure(static): object                                           $builderFactory
     */
    #[DataProvider('dataProviderHandleBuilder')]
    public function testDirective(
        array|Exception $expected,
        Closure $builderFactory,
        mixed $value,
    ): void {
        $path = is_array($expected) ? 'data.test' : 'errors.0.message';
        $body = is_array($expected) ? [] : json_encode($expected->getMessage(), JSON_THROW_ON_ERROR);

        $this
            ->useGraphQLSchema(
                <<<'GRAPHQL'
                type Query {
                    test(input: _ @searchBy): [TestObject!]!
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
                query test($input: SearchByRootTestObject) {
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
     */
    #[DataProvider('dataProviderHandleBuilder')]
    public function testHandleBuilder(
        array|Exception $expected,
        Closure $builderFactory,
        mixed $value,
    ): void {
        $builder   = $builderFactory($this);
        $directive = $this->getExposeBuilderDirective($builder);

        $this->useGraphQLSchema(
            <<<GRAPHQL
            type Query {
                test(input: Test @searchBy): [String!]! {$directive::getName()}
            }

            input Test {
                id: Int!
                value: String @rename(attribute: "renamed.field")
            }
            GRAPHQL,
        );

        $type = match (true) {
            $builder instanceof QueryBuilder => 'SearchByQueryRootTest',
            default                          => 'SearchByRootTest',
        };
        $result = $this->graphQL(
            <<<GRAPHQL
            query test(\$query: {$type}) {
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
                test(search: String @search, input: Test @searchBy): [String!]! {$directive::getName()}
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
            query test($query: SearchByScoutRootTest) {
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
            'Query'                 => [
                'Query.expected.graphql',
                'Query.schema.graphql',
                null,
            ],
            'Explicit'              => [
                'Explicit.expected.graphql',
                'Explicit.schema.graphql',
                null,
            ],
            'Implicit'              => [
                'Implicit.expected.graphql',
                'Implicit.schema.graphql',
                null,
            ],
            'ScalarOperators'       => [
                'ScalarOperators.expected.graphql',
                'ScalarOperators.schema.graphql',
                null,
            ],
            'TypeRegistry'          => [
                'TypeRegistry.expected.graphql',
                'TypeRegistry.schema.graphql',
                static function (TestCase $test): void {
                    $enum    = new EnumType([
                        'name'   => 'TestEnum',
                        'values' => [
                            'a' => [
                                'value'       => 123,
                                'description' => 'description',
                            ],
                        ],
                    ]);
                    $ignored = new class([
                        'name'   => 'TestIgnored',
                        'fields' => [
                            [
                                'name' => 'name',
                                'type' => Type::nonNull(Type::string()),
                            ],
                        ],
                    ]) extends InputObjectType implements Ignored {
                        // empty
                    };
                    $typeA   = new InputObjectType([
                        'name'   => 'TestTypeA',
                        'fields' => [
                            [
                                'name' => 'name',
                                'type' => Type::string(),
                            ],
                            [
                                'name' => 'flag',
                                'type' => Type::boolean(),
                            ],
                            [
                                'name' => 'value',
                                'type' => Type::listOf(Type::nonNull($enum)),
                            ],
                            [
                                'name' => 'ignored',
                                'type' => Type::listOf(Type::nonNull($ignored)),
                            ],
                        ],
                    ]);
                    $typeB   = new InputObjectType([
                        'name'   => 'TestTypeB',
                        'fields' => [
                            [
                                'name' => 'name',
                                'type' => Type::nonNull(Type::string()),
                            ],
                            [
                                'name' => 'child',
                                'type' => $typeA,
                            ],
                        ],
                    ]);

                    $registry = $test->app()->make(TypeRegistry::class);

                    $registry->register($enum);
                    $registry->register($typeA);
                    $registry->register($typeB);
                    $registry->register($ignored);
                },
            ],
            'CustomComplexOperator' => [
                'CustomComplexOperator.expected.graphql',
                'CustomComplexOperator.schema.graphql',
                static function (TestCase $test): void {
                    $locator   = $test->app()->make(DirectiveLocator::class);
                    $resolver  = $test->app()->make(BuilderFieldResolver::class);
                    $directive = new DirectiveTest__CustomComplexOperator($resolver);

                    $locator->setResolved('customComplexOperator', $directive::class);
                },
            ],
            'AllowedDirectives'     => [
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
                            $config->builder->allowedDirectives            = [
                                RenameDirective::class,
                                $allowed::class,
                            ];
                            $config->searchBy->operators['String']         = [
                                SearchByOperatorEqualDirective::class,
                            ];
                            $config->searchBy->operators[Operators::Extra] = [
                                SearchByOperatorFieldDirective::class,
                            ];
                        },
                    );
                },
            ],
            'Ignored'               => [
                'Ignored.expected.graphql',
                'Ignored.schema.graphql',
                static function (TestCase $test): void {
                    $test->setConfiguration(
                        PackageConfig::class,
                        static function (Config $config): void {
                            $config->searchBy->operators['String']         = [
                                SearchByOperatorEqualDirective::class,
                            ];
                            $config->searchBy->operators[Operators::Extra] = [
                                SearchByOperatorFieldDirective::class,
                            ];
                        },
                    );

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
            'Example'               => [
                'Example.expected.graphql',
                'Example.schema.graphql',
                static function (TestCase $test): void {
                    $test->setConfiguration(
                        PackageConfig::class,
                        static function (Config $config): void {
                            $config->searchBy->operators['Date'] = [
                                SearchByOperatorBetweenDirective::class,
                            ];
                        },
                    );
                },
            ],
            'InterfaceUpdate'       => [
                'InterfaceUpdate.expected.graphql',
                'InterfaceUpdate.schema.graphql',
                static function (TestCase $test): void {
                    $test->setConfiguration(
                        PackageConfig::class,
                        static function (Config $config): void {
                            $config->searchBy->operators[Operators::ID]    = [
                                SearchByOperatorEqualDirective::class,
                            ];
                            $config->searchBy->operators[Operators::Extra] = [
                                SearchByOperatorFieldDirective::class,
                            ];
                        },
                    );
                },
            ],
        ];
    }

    /**
     * @return array<array-key, mixed>
     */
    public static function dataProviderHandleBuilder(): array {
        return (new MergeDataProvider([
            'Both'     => new CompositeDataProvider(
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
                    ],
                    'empty operators'             => [
                        new ConditionEmpty(),
                        [
                            'field' => [
                                'id' => [
                                    // empty
                                ],
                            ],
                        ],
                    ],
                    'too many fields (operators)' => [
                        new ConditionTooManyFields(['equal', 'notEqual']),
                        [
                            'field' => [
                                'id' => [
                                    'equal'    => 1,
                                    'notEqual' => 1,
                                ],
                            ],
                        ],
                    ],
                    'too many fields (fields)'    => [
                        new ConditionTooManyFields(['id', 'value']),
                        [
                            'field' => [
                                'id'    => [
                                    'notEqual' => 1,
                                ],
                                'value' => [
                                    'notEqual' => 'a',
                                ],
                            ],
                        ],
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
                    ],
                ]),
            ),
            'Query'    => new CompositeDataProvider(
                new QueryBuilderDataProvider(),
                new ArrayDataProvider([
                    'valid condition' => [
                        [
                            'query'    => <<<'SQL'
                                select
                                    *
                                from
                                    "test_objects"
                                where
                                    (
                                        not (
                                            (
                                                ("id" != ?)
                                                and (
                                                    (
                                                        ("id" = ?)
                                                        or ("renamed"."field" != ?)
                                                    )
                                                )
                                            )
                                        )
                                    )
                            SQL
                            ,
                            'bindings' => [1, 2, 'a'],
                        ],
                        [
                            'not' => [
                                'allOf' => [
                                    [
                                        'field' => [
                                            'id' => [
                                                'notEqual' => 1,
                                            ],
                                        ],
                                    ],
                                    [
                                        'anyOf' => [
                                            [
                                                'field' => [
                                                    'id' => [
                                                        'equal' => 2,
                                                    ],
                                                ],
                                            ],
                                            [
                                                'field' => [
                                                    'value' => [
                                                        'notEqual' => 'a',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]),
            ),
            'Eloquent' => new CompositeDataProvider(
                new EloquentBuilderDataProvider(),
                new ArrayDataProvider([
                    'valid condition' => [
                        [
                            'query'    => <<<'SQL'
                                select
                                    *
                                from
                                    "test_objects"
                                where
                                    (
                                        not (
                                            (
                                                ("test_objects"."id" != ?)
                                                and (
                                                    (
                                                        ("test_objects"."id" = ?)
                                                        or ("test_objects"."renamed"."field" != ?)
                                                    )
                                                )
                                            )
                                        )
                                    )
                            SQL
                            ,
                            'bindings' => [1, 2, 'a'],
                        ],
                        [
                            'not' => [
                                'allOf' => [
                                    [
                                        'field' => [
                                            'id' => [
                                                'notEqual' => 1,
                                            ],
                                        ],
                                    ],
                                    [
                                        'anyOf' => [
                                            [
                                                'field' => [
                                                    'id' => [
                                                        'equal' => 2,
                                                    ],
                                                ],
                                            ],
                                            [
                                                'field' => [
                                                    'value' => [
                                                        'notEqual' => 'a',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]),
            ),
        ]))->getData();
    }

    /**
     * @return array<array-key, mixed>
     */
    public static function dataProviderHandleScoutBuilder(): array {
        return (new CompositeDataProvider(
            new ScoutBuilderDataProvider(),
            new ArrayDataProvider([
                'empty'                       => [
                    [
                        // empty
                    ],
                    [
                        // empty
                    ],
                    null,
                    null,
                ],
                'empty operators'             => [
                    new ConditionEmpty(),
                    [
                        'field' => [
                            'a' => [
                                // empty
                            ],
                        ],
                    ],
                    null,
                    null,
                ],
                'too many fields (operators)' => [
                    new ConditionTooManyFields(['equal', 'in']),
                    [
                        'field' => [
                            'a' => [
                                'equal' => 1,
                                'in'    => [1, 2, 3],
                            ],
                        ],
                    ],
                    null,
                    null,
                ],
                'too many fields (fields)'    => [
                    new ConditionTooManyFields(['a', 'b']),
                    [
                        'field' => [
                            'a' => [
                                'equal' => 1,
                            ],
                            'b' => [
                                'equal' => 'a',
                            ],
                        ],
                    ],
                    null,
                    null,
                ],
                'null'                        => [
                    [
                        // empty
                    ],
                    null,
                    null,
                    null,
                ],
                'default field resolver'      => [
                    [
                        'wheres'   => [
                            'a'   => 1,
                            'c.a' => 2,
                        ],
                        'whereIns' => [
                            'renamed.field' => ['a', 'b', 'c'],
                        ],
                    ],
                    [
                        'allOf' => [
                            [
                                'field' => [
                                    'a' => [
                                        'equal' => 1,
                                    ],
                                ],
                            ],
                            [
                                'field' => [
                                    'b' => [
                                        'in' => ['a', 'b', 'c'],
                                    ],
                                ],
                            ],
                            [
                                'field' => [
                                    'c' => [
                                        'field' => [
                                            'a' => [
                                                'equal' => 2,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    null,
                    null,
                ],
                'resolver'                    => [
                    [
                        'wheres'   => [
                            'a'    => 1,
                            'c__a' => 2,
                        ],
                        'whereIns' => [
                            'renamed.field' => ['a', 'b', 'c'],
                        ],
                    ],
                    [
                        'allOf' => [
                            [
                                'field' => [
                                    'a' => [
                                        'equal' => 1,
                                    ],
                                ],
                            ],
                            [
                                'field' => [
                                    'b' => [
                                        'in' => ['a', 'b', 'c'],
                                    ],
                                ],
                            ],
                            [
                                'field' => [
                                    'c' => [
                                        'field' => [
                                            'a' => [
                                                'equal' => 2,
                                            ],
                                        ],
                                    ],
                                ],
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
class DirectiveTest__CustomComplexOperator extends Operator implements TypeDefinition {
    #[Override]
    public static function getName(): string {
        return 'custom';
    }

    #[Override]
    public function getFieldType(
        TypeProvider $provider,
        TypeSource $source,
        Context $context,
    ): ?string {
        return $provider->getType(static::class, $provider->getTypeSource(Type::int()), $context);
    }

    #[Override]
    public function getFieldDescription(): ?string {
        return 'Custom condition.';
    }

    #[Override]
    public static function definition(): string {
        return <<<'GRAPHQL'
            directive @customComplexOperator(value: String) on INPUT_FIELD_DEFINITION
        GRAPHQL;
    }

    #[Override]
    public function call(
        Handler $handler,
        object $builder,
        Field $field,
        Argument $argument,
        Context $context,
    ): object {
        throw new Exception('Should not be called');
    }

    #[Override]
    public function getTypeName(TypeSource $source, Context $context): string {
        $directiveName = Directive::Name;
        $typeName      = Str::studly($source->getTypeName());

        return "{$directiveName}ComplexCustom{$typeName}";
    }

    #[Override]
    public function getTypeDefinition(
        Manipulator $manipulator,
        TypeSource $source,
        Context $context,
        string $name,
    ): (TypeDefinitionNode&Node)|string|null {
        return Parser::inputObjectTypeDefinition(
            <<<GRAPHQL
            """
            Custom operator
            """
            input {$name} {
                custom: {$source->getTypeName()}
            }
            GRAPHQL,
        );
    }
}
