<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Directives;

use Closure;
use Exception;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionTooManyProperties;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeDefinitionImpossibleToCreateType;
use LastDragon_ru\LaraASP\GraphQL\Package;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\Ignored;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators\Extra\Random;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Types\Clause;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\GraphQLExpectedSchema;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Nuwave\Lighthouse\Scout\SearchDirective;

use function config;
use function is_array;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\GraphQL\SortBy\Directives\Directive
 */
class DirectiveTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderManipulateArgDefinition
     *
     * @param Closure(static): GraphQLExpectedSchema $expected
     * @param Closure(static): void                  $prepare
     */
    public function testManipulateArgDefinition(Closure $expected, string $graphql, ?Closure $prepare = null): void {
        $directives = $this->app->make(DirectiveLocator::class);

        $directives->setResolved('search', SearchDirective::class);

        if ($prepare) {
            $prepare($this);
        }

        self::assertGraphQLSchemaEquals(
            $expected($this),
            $this->getTestData()->file($graphql),
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

        self::assertGraphQLSchemaEquals(
            $this->getTestData()->file('~registry-expected.graphql'),
            $this->getTestData()->file('~registry.graphql'),
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
     * @dataProvider dataProviderHandleBuilder
     *
     * @param array{query: string, bindings: array<mixed>}|Exception $expected
     * @param Closure(static): object                                $builderFactory
     * @param Closure(static): void                                  $prepare
     */
    public function testHandleBuilder(
        array|Exception $expected,
        Closure $builderFactory,
        mixed $value,
        ?Closure $prepare = null,
    ): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        if ($prepare) {
            $prepare($this);
        }

        $this->useGraphQLSchema(
        /** @lang GraphQL */
            <<<'GRAPHQL'
            type Query {
                test(input: Test @sortBy): String! @mock
            }

            input Test {
                a: Int!
                b: String
            }
            GRAPHQL,
        );

        $definitionNode = Parser::inputValueDefinition('input: [SortByClauseTest!]');
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
            'full'    => [
                static function (self $test): GraphQLExpectedSchema {
                    return (new GraphQLExpectedSchema(
                        $test->getTestData()->file('~full-expected.graphql'),
                    ));
                },
                '~full.graphql',
                static function (): void {
                    $package = Package::Name;

                    config([
                        "{$package}.sort_by.operators" => [
                            Operators::Extra => [
                                Random::class,
                            ],
                        ],
                    ]);
                },
            ],
            'example' => [
                static function (self $test): GraphQLExpectedSchema {
                    return (new GraphQLExpectedSchema(
                        $test->getTestData()->file('~example-expected.graphql'),
                    ));
                },
                '~example.graphql',
                null,
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
                    null,
                ],
                'empty operators'     => [
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
                        [
                            // empty
                        ],
                    ],
                    null,
                ],
                'too many properties' => [
                    new ConditionTooManyProperties(['a', 'b']),
                    [
                        [
                            'a' => 'asc',
                            'b' => 'desc',
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
                                "tmp"
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
                                "tmp"
                            order by
                                "a" asc,
                                "b" desc,
                                RANDOM()
                        SQL
                        ,
                        'bindings' => [],
                    ],
                    [
                        [
                            'a' => 'asc',
                        ],
                        [
                            'b' => 'desc',
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
                                    Random::class,
                                ],
                            ],
                        ]);
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
