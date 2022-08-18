<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives;

use Closure;
use Exception;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Config\Repository;
use LastDragon_ru\LaraASP\Eloquent\Testing\Package\Models\TestObject;
use LastDragon_ru\LaraASP\Eloquent\Testing\Package\Models\WithTestObject;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionEmpty;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionTooManyOperators;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionTooManyProperties;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\TypeDefinitionUnknown;
use LastDragon_ru\LaraASP\GraphQL\Package;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\Between;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Complex\Relation;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Property;
use LastDragon_ru\LaraASP\GraphQL\Testing\GraphQLExpectedSchema;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonMatchesFragment;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Bodies\JsonBody;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentTypes\JsonContentType;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Response;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes\Ok;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

use function is_array;
use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive
 */
class DirectiveTest extends TestCase {
    use WithTestObject;
    use MakesGraphQLRequests;

    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::manipulateArgDefinition
     *
     * @dataProvider dataProviderManipulateArgDefinition
     *
     * @param Closure(static): GraphQLExpectedSchema $expected
     * @param Closure(static): void                  $prepare
     */
    public function testManipulateArgDefinition(Closure $expected, string $graphql, ?Closure $prepare = null): void {
        if ($prepare) {
            $prepare($this);
        }

        self::assertGraphQLSchemaEquals(
            $expected($this),
            $this->getTestData()->file($graphql),
        );
    }

    /**
     * @covers ::manipulateArgDefinition
     */
    public function testManipulateArgDefinitionUnknownType(): void {
        self::expectExceptionObject(new TypeDefinitionUnknown('UnknownType'));

        $this->printGraphQLSchema($this->getTestData()->file('~unknown.graphql'));
    }

    /**
     * @covers ::manipulateArgDefinition
     */
    public function testManipulateArgDefinitionProgrammaticallyAddedType(): void {
        $enum  = new EnumType([
            'name'   => 'TestEnum',
            'values' => [
                'property' => [
                    'value'       => 123,
                    'description' => 'test property',
                ],
            ],
        ]);
        $typeA = new InputObjectType([
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
            ],
        ]);
        $typeB = new InputObjectType([
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

        $registry = $this->app->make(TypeRegistry::class);

        $registry->register($enum);
        $registry->register($typeA);
        $registry->register($typeB);

        self::assertGraphQLSchemaEquals(
            $this->getTestData()->file('~programmatically-expected.graphql'),
            $this->getTestData()->file('~programmatically.graphql'),
        );
    }

    /**
     * @covers ::handleBuilder
     *
     * @dataProvider dataProviderHandleBuilder
     *
     * @param array{query: string, bindings: array<mixed>}|Exception $expected
     * @param Closure(static): object                                $builderFactory
     */
    public function testDirective(
        array|Exception $expected,
        Closure $builderFactory,
        mixed $value,
    ): void {
        $model = json_encode(TestObject::class, JSON_THROW_ON_ERROR);
        $path  = is_array($expected) ? 'data.test' : 'errors.0.message';
        $body  = is_array($expected) ? [] : json_encode($expected->getMessage(), JSON_THROW_ON_ERROR);

        $this
            ->useGraphQLSchema(
                /** @lang GraphQL */
                <<<GRAPHQL
                type Query {
                    test(input: Test @searchBy): [TestObject!]!
                    @all(model: {$model})
                }

                input Test {
                    a: Int!
                    b: String
                }

                type TestObject {
                    id: String!
                }
                GRAPHQL,
            )
            ->graphQL(
                /** @lang GraphQL */
                <<<'GRAPHQL'
                query test($input: SearchByConditionTest) {
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
     * @covers ::handleBuilder
     *
     * @dataProvider dataProviderHandleBuilder
     *
     * @param array{query: string, bindings: array<mixed>}|Exception $expected
     * @param Closure(static): object                                $builderFactory
     */
    public function testHandleBuilder(
        array|Exception $expected,
        Closure $builderFactory,
        mixed $value,
    ): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $this->useGraphQLSchema(
            /** @lang GraphQL */
            <<<'GRAPHQL'
            type Query {
                test(input: Test @searchBy): String! @mock
            }

            input Test {
                a: Int!
                b: String
            }
            GRAPHQL,
        );

        $definitionNode = Parser::inputValueDefinition('input: SearchByConditionTest');
        $directiveNode  = Parser::directive('@test');
        $directive      = $this->app->make(Directive::class)->hydrate($directiveNode, $definitionNode);
        $builder        = $builderFactory($this);
        $actual         = $directive->handleBuilder($builder, $value);

        if (is_array($expected)) {
            self::assertInstanceOf($builder::class, $actual);
            self::assertDatabaseQueryEquals($expected, $actual);
        } else {
            self::fail('Something wrong...');
        }
    }

    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<string,array{Closure(self): GraphQLExpectedSchema, string}>
     */
    public function dataProviderManipulateArgDefinition(): array {
        return [
            'full'                           => [
                static function (self $test): GraphQLExpectedSchema {
                    return (new GraphQLExpectedSchema(
                        $test->getTestData()->file('~full-expected.graphql'),
                    ))
                        ->setUnusedTypes([
                            'InputA',
                            'NestedA',
                            'NestedB',
                            'NestedC',
                            'InputB',
                        ]);
                },
                '~full.graphql',
                null,
            ],
            'example'                        => [
                static function (self $test): GraphQLExpectedSchema {
                    return (new GraphQLExpectedSchema(
                        $test->getTestData()->file('~example-expected.graphql'),
                    ));
                },
                '~example.graphql',
                static function (TestCase $test): void {
                    $package = Package::Name;
                    $config  = $test->app->make(Repository::class);

                    $config->set("{$package}.search_by.scalars.Date", [
                        Between::class,
                    ]);
                },
            ],
            'only used type should be added' => [
                static function (self $test): GraphQLExpectedSchema {
                    return (new GraphQLExpectedSchema(
                        $test->getTestData()->file('~usedonly-expected.graphql'),
                    ))
                        ->setUnusedTypes([
                            'Properties',
                            'Float',
                            'Int',
                            'Boolean',
                        ]);
                },
                '~usedonly.graphql',
                null,
            ],
            'custom complex operators'       => [
                static function (self $test): GraphQLExpectedSchema {
                    return (new GraphQLExpectedSchema(
                        $test->getTestData()->file('~custom-complex-operators-expected.graphql'),
                    ));
                },
                '~custom-complex-operators.graphql',
                static function (TestCase $test): void {
                    $locator   = $test->app->make(DirectiveLocator::class);
                    $property  = $test->app->make(Property::class);
                    $directive = new class($property) extends Relation {
                        public static function getName(): string {
                            return 'custom';
                        }

                        public function getFieldDescription(): string {
                            return 'Custom condition.';
                        }

                        public static function getDirectiveName(): string {
                            return '@customComplexOperator';
                        }

                        public static function definition(): string {
                            $name = static::getDirectiveName();

                            return /** @lang GraphQL */ <<<GRAPHQL
                                directive ${name}(value: String) on INPUT_FIELD_DEFINITION
                            GRAPHQL;
                        }
                    };

                    $locator->setResolved('customComplexOperator', $directive::class);
                },
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    public function dataProviderHandleBuilder(): array {
        return (new CompositeDataProvider(
            new BuilderDataProvider(),
            new ArrayDataProvider([
                'empty'               => [
                    [
                        'query'    => <<<'SQL'
                            select
                                *
                            from
                                "tmp"
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
                        'a' => [
                            // empty
                        ],
                    ],
                ],
                'too many properties' => [
                    new ConditionTooManyProperties(['a', 'b']),
                    [
                        'a' => [
                            'notEqual' => 1,
                        ],
                        'b' => [
                            'notEqual' => 'a',
                        ],
                    ],
                ],
                'too many operators'  => [
                    new ConditionTooManyOperators(['equal', 'notEqual']),
                    [
                        'a' => [
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
                                "tmp"
                        SQL
                        ,
                        'bindings' => [],
                    ],
                    null,
                ],
                'valid condition'     => [
                    [
                        'query'    => <<<'SQL'
                            select
                                *
                            from
                                "tmp"
                            where
                                (
                                    not (
                                        (
                                            ("a" != ?)
                                            and (
                                                (
                                                    ("a" = ?)
                                                    or ("b" != ?)
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
        ))->getData();
    }
    // </editor-fold>
}
