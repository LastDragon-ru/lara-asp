<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter;

use GraphQL\Language\Parser;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Settings\DefaultSettings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Settings\GraphQLSettings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\GraphQLExpectedSchema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\GraphQLExpectedType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\GraphQLPrinter\Printer
 */
class PrinterTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderPrintSchema
     */
    public function testPrintSchema(GraphQLExpectedSchema $expected, ?Settings $settings, int $level): void {
        $printer = (new Printer())->setSettings($settings)->setLevel($level);
        $schema  = $this->getGraphQLSchema($this->getTestData()->file('~schema.graphql'));
        $actual  = $printer->printSchema($schema);

        $this->assertGraphQLSchemaEquals($expected, $actual);
    }

    /**
     * @dataProvider dataProviderPrintSchemaType
     */
    public function testPrintSchemaType(
        GraphQLExpectedType $expected,
        ?Settings $settings,
        int $level,
        Type|string $type,
    ): void {
        $printer = (new Printer())->setSettings($settings)->setLevel($level);
        $schema  = $this->getGraphQLSchema($this->getTestData()->file('~schema.graphql'));
        $actual  = $printer->printSchemaType($schema, $type);

        $this->assertGraphQLSchemaTypeEquals($expected, $actual, $schema);
    }

    /**
     * @dataProvider dataProviderPrintType
     */
    public function testPrintType(GraphQLExpectedType $expected, ?Settings $settings, int $level, Type $type): void {
        $printer = (new Printer())->setSettings($settings)->setLevel($level);
        $actual  = $printer->printType($type);

        $this->assertGraphQLTypeEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array<mixed>>
     */
    public function dataProviderPrintSchema(): array {
        return [
            'null'                                             => [
                (new GraphQLExpectedSchema(
                    $this->getTestData()->file('~printSchema-DefaultSettings.graphql'),
                ))
                    ->setUsedTypes([
                        'Query',
                        'String',
                        'Enum',
                        'Int',
                        'Float',
                        'InputA',
                        'InterfaceC',
                        'Scalar',
                        'TypeB',
                        'Mutation',
                        'TypeA',
                        'Union',
                        'TypeC',
                        'Subscription',
                    ])
                    ->setUsedDirectives([
                        '@deprecated',
                    ]),
                null,
                0,
            ],
            DefaultSettings::class                             => [
                (new GraphQLExpectedSchema(
                    $this->getTestData()->file('~printSchema-DefaultSettings.graphql'),
                ))
                    ->setUsedTypes([
                        'Query',
                        'String',
                        'Enum',
                        'Int',
                        'Float',
                        'InputA',
                        'InterfaceC',
                        'Scalar',
                        'TypeB',
                        'Mutation',
                        'TypeA',
                        'Union',
                        'TypeC',
                        'Subscription',
                    ])
                    ->setUsedDirectives([
                        '@deprecated',
                    ]),
                new DefaultSettings(),
                0,
            ],
            GraphQLSettings::class                             => [
                (new GraphQLExpectedSchema(
                    $this->getTestData()->file('~printSchema-GraphQLSettings.graphql'),
                ))
                    ->setUsedTypes([
                        'Query',
                        'String',
                        'Enum',
                        'EnumUnused',
                        'Int',
                        'Float',
                        'InputA',
                        'InputUnused',
                        'InterfaceA',
                        'InterfaceB',
                        'InterfaceC',
                        'InterfaceUnused',
                        'TypeUnused',
                        'Scalar',
                        'ScalarUnused',
                        'TypeB',
                        'Mutation',
                        'TypeA',
                        'Union',
                        'TypeC',
                        'Subscription',
                        'UnionUnused',
                    ])
                    ->setUsedDirectives([
                        '@deprecated',
                    ]),
                new GraphQLSettings(),
                0,
            ],
            TestSettings::class                                => [
                (new GraphQLExpectedSchema(
                    $this->getTestData()->file('~printSchema-TestSettings.graphql'),
                ))
                    ->setUsedTypes([
                        'Int',
                        'Query',
                        'String',
                        'Enum',
                        'Float',
                        'InputA',
                        'InterfaceC',
                        'Scalar',
                        'TypeB',
                        'Mutation',
                        'TypeA',
                        'Union',
                        'TypeC',
                        'Subscription',
                    ])
                    ->setUsedDirectives([
                        '@deprecated',
                        '@directive',
                    ]),
                new TestSettings(),
                0,
            ],
            TestSettings::class.' (no directives definitions)' => [
                (new GraphQLExpectedSchema(
                    $this->getTestData()->file('~printSchema-TestSettings-NoDirectivesDefinitions.graphql'),
                ))
                    ->setUsedTypes([
                        'Query',
                        'String',
                        'Enum',
                        'Int',
                        'Float',
                        'InputA',
                        'InterfaceC',
                        'Scalar',
                        'TypeB',
                        'Mutation',
                        'TypeA',
                        'Union',
                        'TypeC',
                        'Subscription',
                    ])
                    ->setUsedDirectives([
                        '@deprecated',
                        '@directive',
                    ]),
                (new TestSettings())
                    ->setPrintDirectiveDefinitions(false),
                0,
            ],
            TestSettings::class.' (no normalization)'          => [
                (new GraphQLExpectedSchema(
                    $this->getTestData()->file('~printSchema-TestSettings-NoNormalization.graphql'),
                ))
                    ->setUsedTypes([
                        'Int',
                        'Query',
                        'String',
                        'TypeC',
                        'Subscription',
                        'Float',
                        'TypeB',
                        'InputA',
                        'Mutation',
                        'TypeA',
                        'Enum',
                        'Union',
                        'Scalar',
                        'InterfaceC',
                    ])
                    ->setUsedDirectives([
                        '@deprecated',
                        '@directive',
                    ]),
                (new TestSettings())
                    ->setNormalizeSchema(false)
                    ->setNormalizeUnions(false)
                    ->setNormalizeEnums(false)
                    ->setNormalizeInterfaces(false)
                    ->setNormalizeFields(false)
                    ->setNormalizeArguments(false)
                    ->setNormalizeDescription(false)
                    ->setNormalizeDirectiveLocations(false)
                    ->setAlwaysMultilineUnions(false)
                    ->setAlwaysMultilineInterfaces(false)
                    ->setAlwaysMultilineDirectiveLocations(false),
                0,
            ],
            TestSettings::class.' (DirectiveDefinitionFilter)' => [
                (new GraphQLExpectedSchema(
                    $this->getTestData()->file('~printSchema-TestSettings-DirectiveDefinitionFilter.graphql'),
                ))
                    ->setUsedTypes([
                        'Query',
                        'String',
                        'Enum',
                        'Int',
                        'Float',
                        'InputA',
                        'InterfaceC',
                        'Scalar',
                        'TypeB',
                        'Mutation',
                        'TypeA',
                        'Union',
                        'TypeC',
                        'Subscription',
                    ])
                    ->setUsedDirectives([
                        '@deprecated',
                        '@directive',
                    ]),
                (new TestSettings())
                    ->setDirectiveDefinitionFilter(
                        static function (string $directive, bool $isStandard): bool {
                            return $isStandard || $directive !== 'directive';
                        },
                    ),
                0,
            ],
            TestSettings::class.' (TypeDefinitionFilter)'      => [
                (new GraphQLExpectedSchema(
                    $this->getTestData()->file('~printSchema-TestSettings-TypeDefinitionFilter.graphql'),
                ))
                    ->setUsedTypes([
                        'Query',
                        'String',
                        'Enum',
                        'Int',
                        'Float',
                        'InputA',
                        'InterfaceC',
                        'Scalar',
                        'TypeB',
                        'Mutation',
                        'TypeA',
                        'Union',
                        'TypeC',
                    ])
                    ->setUsedDirectives([
                        '@deprecated',
                        '@directive',
                    ]),
                (new TestSettings())
                    ->setTypeDefinitionFilter(
                        static function (Type $type, bool $isStandard): bool {
                            return $isStandard === false
                                && $type->name !== 'Subscription';
                        },
                    ),
                0,
            ],
            TestSettings::class.' (everything)'                => [
                (new GraphQLExpectedSchema(
                    $this->getTestData()->file('~printSchema-TestSettings-Everything.graphql'),
                ))
                    ->setUsedTypes([
                        'Int',
                        'Query',
                        'Enum',
                        'Int',
                        'Float',
                        'InputA',
                        'InterfaceC',
                        'Scalar',
                        'TypeB',
                        'Mutation',
                        'TypeA',
                        'Union',
                        'TypeC',
                        'Subscription',
                        'String',
                    ])
                    ->setUsedDirectives([
                        '@deprecated',
                        '@directive',
                    ]),
                (new TestSettings())
                    ->setTypeDefinitionFilter(static fn (): bool => true)
                    ->setDirectiveFilter(static fn (): bool => true)
                    ->setDirectiveDefinitionFilter(static fn (): bool => true),
                0,
            ],
        ];
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function dataProviderPrintSchemaType(): array {
        return [
            UnionType::class => [
                (new GraphQLExpectedType(
                    $this->getTestData()->file('~printSchemaType-UnionType.graphql'),
                ))
                    ->setUsedTypes([
                        'String',
                        'InterfaceC',
                        'Int',
                        'Float',
                        'TypeA',
                        'Union',
                    ])
                    ->setUsedDirectives([
                        '@deprecated',
                        '@directive',
                    ]),
                new TestSettings(),
                0,
                new UnionType([
                    'name'  => 'Union',
                    'types' => [
                        new ObjectType([
                            'name' => 'TypeA',
                        ]),
                    ],
                ]),
            ],
            'TypeA'          => [
                (new GraphQLExpectedType(
                    $this->getTestData()->file('~printSchemaType-TypeA.graphql'),
                ))
                    ->setUsedTypes([
                        'String',
                        'InterfaceC',
                        'Int',
                        'Float',
                        'TypeA',
                    ])
                    ->setUsedDirectives([
                        '@deprecated',
                        '@directive',
                    ]),
                new TestSettings(),
                0,
                'TypeA',
            ],
            'InterfaceC'     => [
                (new GraphQLExpectedType(
                    $this->getTestData()->file('~printSchemaType-InterfaceC.graphql'),
                ))
                    ->setUsedTypes([
                        'String',
                        'Int',
                        'Float',
                        'InterfaceC',
                    ])
                    ->setUsedDirectives([
                        // empty
                    ]),
                new TestSettings(),
                1,
                'InterfaceC',
            ],
        ];
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function dataProviderPrintType(): array {
        return [
            UnionType::class       => [
                (new GraphQLExpectedType(
                /** @lang GraphQL */
                    <<<'GRAPHQL'
                        union CodeUnion =
                            | CodeType

                    GRAPHQL,
                ))
                    ->setUsedTypes([
                        'CodeType',
                        'CodeUnion',
                    ])
                    ->setUsedDirectives([
                        // empty
                    ]),
                new TestSettings(),
                1,
                new UnionType([
                    'name'  => 'CodeUnion',
                    'types' => [
                        new ObjectType([
                            'name' => 'CodeType',
                        ]),
                    ],
                ]),
            ],
            InputObjectType::class => [
                (new GraphQLExpectedType(
                /** @lang GraphQL */
                    <<<'GRAPHQL'
                    """
                    Description
                    """
                    input CodeInput
                    @schemaDirective
                    {
                        a: Boolean
                    }

                    GRAPHQL,
                ))
                    ->setUsedTypes([
                        'Boolean',
                        'CodeInput',
                    ])
                    ->setUsedDirectives([
                        '@schemaDirective',
                    ]),
                new TestSettings(),
                0,
                new InputObjectType([
                    'name'        => 'CodeInput',
                    'astNode'     => Parser::inputObjectTypeDefinition('input InputObjectType @schemaDirective'),
                    'description' => 'Description',
                    'fields'      => [
                        [
                            'name' => 'a',
                            'type' => Type::boolean(),
                        ],
                    ],
                ]),
            ],
            InterfaceType::class   => [
                (new GraphQLExpectedType(
                /** @lang GraphQL */
                    <<<'GRAPHQL'
                    """
                    Description
                    """
                    interface CodeInterface {
                        a: Boolean!
                    }

                    GRAPHQL,
                ))
                    ->setUsedTypes([
                        'Boolean',
                        'CodeInterface',
                    ])
                    ->setUsedDirectives([
                        // empty
                    ]),
                (new TestSettings())
                    ->setPrintDirectives(false),
                0,
                new InterfaceType([
                    'name'        => 'CodeInterface',
                    'astNode'     => Parser::interfaceTypeDefinition('interface CodeInterface @codeDirective'),
                    'description' => 'Description',
                    'fields'      => [
                        [
                            'name' => 'a',
                            'type' => Type::nonNull(Type::boolean()),
                        ],
                    ],
                ]),
            ],
        ];
    }
    // </editor-fold>
}
