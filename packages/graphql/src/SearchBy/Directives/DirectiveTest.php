<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives;

use Closure;
use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use Exception;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Str;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\BuilderPropertyResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scout\FieldResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionEmpty;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionTooManyOperators;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionTooManyProperties;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\TypeDefinitionUnknown;
use LastDragon_ru\LaraASP\GraphQL\Package;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Ignored;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorBetweenDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorEqualDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\BaseOperator;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Data\Models\WithTestObject;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\EloquentBuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\QueryBuilderDataProvider;
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
use LastDragon_ru\LaraASP\Testing\Providers\MergeDataProvider;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Nuwave\Lighthouse\Scout\SearchDirective;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Override;
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
        $directives = Container::getInstance()->make(DirectiveLocator::class);

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

    public function testManipulateArgDefinitionUnknownType(): void {
        self::expectExceptionObject(new TypeDefinitionUnknown('UnknownType'));

        $this->useGraphQLSchema(self::getTestData()->file('~unknown.graphql'));
    }

    public function testManipulateArgDefinitionProgrammaticallyAddedType(): void {
        $enum    = new EnumType([
            'name'   => 'TestEnum',
            'values' => [
                'property' => [
                    'value'       => 123,
                    'description' => 'test property',
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

        $registry = Container::getInstance()->make(TypeRegistry::class);

        $registry->register($enum);
        $registry->register($typeA);
        $registry->register($typeB);
        $registry->register($ignored);

        $this->useGraphQLSchema(
            self::getTestData()->file('~programmatically.graphql'),
        );

        self::assertGraphQLSchemaEquals(
            self::getTestData()->file('~programmatically-expected.graphql'),
        );
    }

    /**
     * @dataProvider dataProviderHandleBuilder
     *
     * @param array{query: string, bindings: array<array-key, mixed>}|Exception $expected
     * @param Closure(static): object                                           $builderFactory
     */
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
                query test($input: SearchByConditionTestObject) {
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
     */
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
            $builder instanceof QueryBuilder => 'SearchByQueryConditionTest',
            default                          => 'SearchByConditionTest',
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

        Container::getInstance()->make(DirectiveLocator::class)
            ->setResolved('search', SearchDirective::class);

        if ($fieldResolver) {
            $this->override(FieldResolver::class, $fieldResolver);
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
            query test($query: SearchByScoutConditionTest) {
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
            'full'                           => [
                static function (self $test): GraphQLExpected {
                    $satisfies = static function (string $version): bool {
                        return InstalledVersions::satisfies(new VersionParser(), 'laravel/scout', $version);
                    };
                    $file      = match (true) {
                        $satisfies('>=10.3.0') => '~full-expected-scout-v10.3.0.graphql',
                        default                => '~full-expected.graphql',
                    };

                    return new GraphQLExpected($test::getTestData()->file($file));
                },
                '~full.graphql',
                static function (TestCase $test): void {
                    $package = Package::Name;

                    config([
                        "{$package}.search_by.operators.Date" => [
                            SearchByOperatorEqualDirective::class,
                        ],
                    ]);
                },
            ],
            'example'                        => [
                static function (self $test): GraphQLExpected {
                    return (new GraphQLExpected(
                        $test::getTestData()->file('~example-expected.graphql'),
                    ));
                },
                '~example.graphql',
                static function (TestCase $test): void {
                    $package = Package::Name;

                    config([
                        "{$package}.search_by.operators.Date" => [
                            SearchByOperatorBetweenDirective::class,
                        ],
                    ]);
                },
            ],
            'only used type should be added' => [
                static function (self $test): GraphQLExpected {
                    return (new GraphQLExpected(
                        $test::getTestData()->file('~usedonly-expected.graphql'),
                    ));
                },
                '~usedonly.graphql',
                null,
            ],
            'custom complex operators'       => [
                static function (self $test): GraphQLExpected {
                    return (new GraphQLExpected(
                        $test::getTestData()->file('~custom-complex-operators-expected.graphql'),
                    ));
                },
                '~custom-complex-operators.graphql',
                static function (): void {
                    $locator   = Container::getInstance()->make(DirectiveLocator::class);
                    $resolver  = Container::getInstance()->make(BuilderPropertyResolver::class);
                    $directive = new class($resolver) extends BaseOperator implements TypeDefinition {
                        #[Override]
                        public static function getName(): string {
                            return 'custom';
                        }

                        #[Override]
                        public function getFieldType(
                            TypeProvider $provider,
                            TypeSource $source,
                            Context $context,
                        ): string {
                            return $provider->getType(static::class, $provider->getTypeSource(Type::int()), $context);
                        }

                        #[Override]
                        public function getFieldDescription(): string {
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
                            Property $property,
                            Argument $argument,
                            Context $context,
                        ): object {
                            throw new Exception('should not be called');
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
                        ): TypeDefinitionNode&Node {
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
                    };

                    $locator->setResolved('customComplexOperator', $directive::class);
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
                    ],
                    'empty operators'     => [
                        new ConditionEmpty(),
                        [
                            'id' => [
                                // empty
                            ],
                        ],
                    ],
                    'too many properties' => [
                        new ConditionTooManyProperties(['id', 'value']),
                        [
                            'id'    => [
                                'notEqual' => 1,
                            ],
                            'value' => [
                                'notEqual' => 'a',
                            ],
                        ],
                    ],
                    'too many operators'  => [
                        new ConditionTooManyOperators(['equal', 'notEqual']),
                        [
                            'id' => [
                                'equal'    => 1,
                                'notEqual' => 1,
                            ],
                        ],
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
                                        'id' => [
                                            'notEqual' => 1,
                                        ],
                                    ],
                                    [
                                        'anyOf' => [
                                            [
                                                'id' => [
                                                    'equal' => 2,
                                                ],
                                            ],
                                            [
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
                                        'id' => [
                                            'notEqual' => 1,
                                        ],
                                    ],
                                    [
                                        'anyOf' => [
                                            [
                                                'id' => [
                                                    'equal' => 2,
                                                ],
                                            ],
                                            [
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
                    new ConditionEmpty(),
                    [
                        'a' => [
                            // empty
                        ],
                    ],
                    null,
                ],
                'too many properties'    => [
                    new ConditionTooManyProperties(['a', 'b']),
                    [
                        'a' => [
                            'equal' => 1,
                        ],
                        'b' => [
                            'equal' => 'a',
                        ],
                    ],
                    null,
                ],
                'too many operators'     => [
                    new ConditionTooManyOperators(['equal', 'in']),
                    [
                        'a' => [
                            'equal' => 1,
                            'in'    => [1, 2, 3],
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
                                'a' => [
                                    'equal' => 1,
                                ],
                            ],
                            [
                                'b' => [
                                    'in' => ['a', 'b', 'c'],
                                ],
                            ],
                            [
                                'c' => [
                                    'a' => [
                                        'equal' => 2,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    null,
                ],
                'custom field resolver'  => [
                    [
                        'wheres'   => [
                            'properties/a'   => 1,
                            'properties/c/a' => 2,
                        ],
                        'whereIns' => [
                            'properties/renamed.field' => ['a', 'b', 'c'],
                        ],
                    ],
                    [
                        'allOf' => [
                            [
                                'a' => [
                                    'equal' => 1,
                                ],
                            ],
                            [
                                'b' => [
                                    'in' => ['a', 'b', 'c'],
                                ],
                            ],
                            [
                                'c' => [
                                    'a' => [
                                        'equal' => 2,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    static function (): FieldResolver {
                        return new class() implements FieldResolver {
                            /**
                             * @inheritDoc
                             */
                            #[Override]
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
class DirectiveTest__Resolver {
    public function __invoke(): mixed {
        throw new Exception('should not be called.');
    }
}
