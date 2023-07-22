<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Printer;

use Exception;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Printer;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Settings\DefaultSettings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Settings\GraphQLSettings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\GraphQLExpectedSchema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\GraphQLExpectedType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\TestSettings;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use PHPUnit\Framework\Attributes\CoversClass;

use function in_array;
use function str_starts_with;

/**
 * @internal
 */
#[CoversClass(Printer::class)]
class PrinterTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderPrintSchema
     */
    public function testPrintSchema(GraphQLExpectedSchema $expected, ?Settings $settings, int $level): void {
        // Types
        $directives = $this->app->make(DirectiveLocator::class);
        $registry   = $this->app->make(TypeRegistry::class);
        $directive  = (new class() extends BaseDirective {
            public static function definition(): string {
                throw new Exception('Should not be called.');
            }
        })::class;

        $codeScalar    = new StringType([
            'name' => 'CodeScalar',
        ]);
        $codeEnum      = new EnumType([
            'name'   => 'CodeEnum',
            'values' => ['C', 'B', 'A'],
        ]);
        $codeInterface = new InterfaceType([
            'name'        => 'CodeInterface',
            'astNode'     => Parser::interfaceTypeDefinition('interface CodeInterface @codeDirective'),
            'description' => 'Description',
            'fields'      => [
                'a' => [
                    'type' => Type::nonNull(Type::boolean()),
                ],
            ],
        ]);
        $codeType      = new ObjectType([
            'name'        => 'CodeType',
            'astNode'     => Parser::objectTypeDefinition('type CodeType @schemaDirective'),
            'description' => 'Description',
            'fields'      => [
                'a' => [
                    'type' => Type::boolean(),
                ],
            ],
        ]);
        $codeUnion     = new UnionType([
            'name'  => 'CodeUnion',
            'types' => [
                $codeType,
            ],
        ]);
        $codeInput     = new InputObjectType([
            'name'        => 'CodeInput',
            'astNode'     => Parser::inputObjectTypeDefinition('input InputObjectType @schemaDirective'),
            'description' => 'Description',
            'fields'      => [
                'a' => [
                    'type' => Type::boolean(),
                ],
            ],
        ]);

        $directives->setResolved('schemaDirective', $directive);
        $directives->setResolved('schemaDirectiveUnused', $directive);
        $directives->setResolved(
            'codeDirective',
            (new class() extends BaseDirective {
                public static function definition(): string {
                    return <<<'GRAPHQL'
                    directive @codeDirective(
                        enum: CodeDirectiveEnum
                        input: CodeDirectiveInput
                        scalar: CodeDirectiveScalar!
                        custom: [CodeDirectiveScalarCustomClass]
                    )
                    repeatable on
                        | INTERFACE
                        | SCALAR
                        | SCHEMA

                    enum CodeDirectiveEnum {
                        A
                        B
                        C
                    }

                    input CodeDirectiveInput {
                        a: Int!
                    }

                    scalar CodeDirectiveScalar

                    scalar CodeDirectiveScalarCustomClass
                    @scalar(class: "GraphQL\\Type\\Definition\\StringType")
                    GRAPHQL;
                }
            })::class,
        );
        $registry->register($codeScalar);
        $registry->register($codeEnum);
        $registry->register($codeInterface);
        $registry->register($codeType);
        $registry->register($codeUnion);
        $registry->register($codeInput);

        // Schema
        $this->useGraphQLSchema(
            self::getTestData()->file('~printSchema-schema.graphql'),
        );

        // Test
        $printer = $this->app->make(Printer::class)->setSettings($settings)->setLevel($level);
        $schema  = $this->getGraphQLSchemaBuilder()->schema();
        $actual  = $printer->printSchema($schema);

        $this->assertGraphQLPrintableEquals($expected, $actual);
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
        // Types
        $directives = $this->app->make(DirectiveLocator::class);
        $registry   = $this->app->make(TypeRegistry::class);
        $directive  = (new class() extends BaseDirective {
            public static function definition(): string {
                throw new Exception('Should not be called.');
            }
        })::class;

        $codeScalar    = new StringType([
            'name' => 'CodeScalar',
        ]);
        $codeEnum      = new EnumType([
            'name'   => 'CodeEnum',
            'values' => ['C', 'B', 'A'],
        ]);
        $codeInterface = new InterfaceType([
            'name'        => 'CodeInterface',
            'astNode'     => Parser::interfaceTypeDefinition('interface CodeInterface @codeDirective'),
            'description' => 'Description',
            'fields'      => [
                [
                    'name' => 'a',
                    'type' => Type::nonNull(Type::boolean()),
                ],
            ],
        ]);
        $codeType      = new ObjectType([
            'name'        => 'CodeType',
            'astNode'     => Parser::objectTypeDefinition('type CodeType @schemaDirective'),
            'description' => 'Description',
            'fields'      => [
                'a' => [
                    'type' => Type::boolean(),
                ],
            ],
        ]);
        $codeUnion     = new UnionType([
            'name'  => 'CodeUnion',
            'types' => [
                $codeType,
            ],
        ]);
        $codeInput     = new InputObjectType([
            'name'        => 'CodeInput',
            'astNode'     => Parser::inputObjectTypeDefinition('input InputObjectType @schemaDirective'),
            'description' => 'Description',
            'fields'      => [
                'a' => [
                    'type' => Type::boolean(),
                ],
            ],
        ]);

        $directives->setResolved('schemaDirective', $directive);
        $directives->setResolved('schemaDirectiveUnused', $directive);
        $directives->setResolved(
            'codeDirective',
            (new class() extends BaseDirective {
                public static function definition(): string {
                    return <<<'GRAPHQL'
                    directive @codeDirective(
                        enum: CodeDirectiveEnum
                        input: CodeDirectiveInput
                        scalar: CodeDirectiveScalar!
                        custom: [CodeDirectiveScalarCustomClass]
                    )
                    repeatable on
                        | INTERFACE
                        | SCALAR
                        | SCHEMA

                    enum CodeDirectiveEnum {
                        A
                        B
                        C
                    }

                    input CodeDirectiveInput {
                        a: Int!
                    }

                    scalar CodeDirectiveScalar

                    scalar CodeDirectiveScalarCustomClass
                    @scalar(class: "GraphQL\\Type\\Definition\\StringType")
                    GRAPHQL;
                }
            })::class,
        );
        $registry->register($codeScalar);
        $registry->register($codeEnum);
        $registry->register($codeInterface);
        $registry->register($codeType);
        $registry->register($codeUnion);
        $registry->register($codeInput);

        // Schema
        $this->useGraphQLSchema(
            self::getTestData()->file('~printSchemaType-schema.graphql'),
        );

        // Test
        $printer = $this->app->make(Printer::class)->setSettings($settings)->setLevel($level);
        $schema  = $this->getGraphQLSchemaBuilder()->schema();
        $actual  = $printer->printSchemaType($schema, $type);

        $this->assertGraphQLExportableEquals($expected, $actual);
    }

    /**
     * @dataProvider dataProviderPrintType
     */
    public function testPrintType(GraphQLExpectedType $expected, ?Settings $settings, int $level, Type $type): void {
        $printer = $this->app->make(Printer::class)->setSettings($settings)->setLevel($level);
        $actual  = $printer->printType($type);

        $this->assertGraphQLPrintableEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array<mixed>>
     */
    public static function dataProviderPrintSchema(): array {
        return [
            'null'                                             => [
                (new GraphQLExpectedSchema(
                    self::getTestData()->file('~printSchema-DefaultSettings.graphql'),
                ))
                    ->setUsedTypes([
                        'Query',
                        'String',
                        'Boolean',
                        'SchemaType',
                        'SchemaEnum',
                        'SchemaInput',
                        'SchemaUnion',
                        'SchemaScalar',
                        'SchemaInterfaceA',
                        'SchemaInterfaceB',
                        'CodeScalar',
                        'CodeInput',
                        'CodeUnion',
                        'CodeEnum',
                        'CodeType',
                        'CodeInterface',
                    ])
                    ->setUsedDirectives([
                        '@deprecated',
                    ]),
                null,
                0,
            ],
            DefaultSettings::class                             => [
                (new GraphQLExpectedSchema(
                    self::getTestData()->file('~printSchema-DefaultSettings.graphql'),
                ))
                    ->setUsedTypes([
                        'Query',
                        'String',
                        'Boolean',
                        'SchemaType',
                        'SchemaEnum',
                        'SchemaInput',
                        'SchemaUnion',
                        'SchemaScalar',
                        'SchemaInterfaceA',
                        'SchemaInterfaceB',
                        'CodeScalar',
                        'CodeInput',
                        'CodeUnion',
                        'CodeEnum',
                        'CodeType',
                        'CodeInterface',
                    ])
                    ->setUsedDirectives([
                        '@deprecated',
                    ]),
                new DefaultSettings(),
                0,
            ],
            GraphQLSettings::class                             => [
                (new GraphQLExpectedSchema(
                    self::getTestData()->file('~printSchema-GraphQLSettings.graphql'),
                ))
                    ->setUsedTypes([
                        'Query',
                        'String',
                        'Boolean',
                        'SchemaType',
                        'SchemaEnum',
                        'SchemaInput',
                        'SchemaUnion',
                        'SchemaScalar',
                        'SchemaInterfaceB',
                        'CodeScalar',
                        'CodeInput',
                        'CodeUnion',
                        'CodeEnum',
                        'CodeType',
                        'SchemaTypeUnused',
                        'SchemaEnumUnused',
                        'SchemaScalarUnused',
                        'CodeInterface',
                        'SchemaInputUnused',
                        'SchemaInterfaceA',
                        'SchemaInterfaceUnused',
                        'SchemaUnionUnused',
                    ])
                    ->setUsedDirectives([
                        '@deprecated',
                    ]),
                new GraphQLSettings(),
                0,
            ],
            TestSettings::class                                => [
                (new GraphQLExpectedSchema(
                    self::getTestData()->file('~printSchema-TestSettings.graphql'),
                ))
                    ->setUsedTypes([
                        'Int',
                        'Query',
                        'String',
                        'Boolean',
                        'SchemaType',
                        'SchemaEnum',
                        'SchemaInput',
                        'SchemaUnion',
                        'SchemaScalar',
                        'SchemaInterfaceA',
                        'SchemaInterfaceB',
                        'CodeScalar',
                        'CodeInput',
                        'CodeUnion',
                        'CodeEnum',
                        'CodeType',
                        'CodeDirectiveEnum',
                        'CodeDirectiveInput',
                        'CodeDirectiveScalar',
                        'CodeDirectiveScalarCustomClass',
                        'CodeInterface',
                    ])
                    ->setUsedDirectives([
                        '@schemaDirective',
                        '@codeDirective',
                        '@deprecated',
                        '@scalar',
                        '@mock',
                    ]),
                new TestSettings(),
                0,
            ],
            TestSettings::class.' (no directives definitions)' => [
                (new GraphQLExpectedSchema(
                    self::getTestData()->file('~printSchema-TestSettings-NoDirectivesDefinitions.graphql'),
                ))
                    ->setUsedTypes([
                        'Query',
                        'String',
                        'Boolean',
                        'SchemaType',
                        'SchemaEnum',
                        'SchemaInput',
                        'SchemaUnion',
                        'SchemaScalar',
                        'SchemaInterfaceA',
                        'SchemaInterfaceB',
                        'CodeScalar',
                        'CodeInput',
                        'CodeUnion',
                        'CodeEnum',
                        'CodeType',
                        'CodeInterface',
                    ])
                    ->setUsedDirectives([
                        '@schemaDirective',
                        '@codeDirective',
                        '@deprecated',
                        '@scalar',
                        '@mock',
                    ]),
                (new TestSettings())
                    ->setPrintDirectiveDefinitions(false),
                0,
            ],
            TestSettings::class.' (no normalization)'          => [
                (new GraphQLExpectedSchema(
                    self::getTestData()->file('~printSchema-TestSettings-NoNormalization.graphql'),
                ))
                    ->setUsedTypes([
                        'Int',
                        'Query',
                        'String',
                        'Boolean',
                        'SchemaType',
                        'SchemaEnum',
                        'SchemaInput',
                        'SchemaUnion',
                        'SchemaScalar',
                        'SchemaInterfaceA',
                        'SchemaInterfaceB',
                        'CodeScalar',
                        'CodeInput',
                        'CodeUnion',
                        'CodeEnum',
                        'CodeType',
                        'CodeDirectiveEnum',
                        'CodeDirectiveInput',
                        'CodeDirectiveScalar',
                        'CodeDirectiveScalarCustomClass',
                        'CodeInterface',
                    ])
                    ->setUsedDirectives([
                        '@schemaDirective',
                        '@codeDirective',
                        '@deprecated',
                        '@scalar',
                        '@mock',
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
                    self::getTestData()->file('~printSchema-TestSettings-DirectiveDefinitionFilter.graphql'),
                ))
                    ->setUsedTypes([
                        'Query',
                        'String',
                        'Boolean',
                        'SchemaType',
                        'SchemaEnum',
                        'SchemaInput',
                        'SchemaUnion',
                        'SchemaScalar',
                        'SchemaInterfaceA',
                        'SchemaInterfaceB',
                        'CodeScalar',
                        'CodeInput',
                        'CodeUnion',
                        'CodeEnum',
                        'CodeType',
                        'CodeInterface',
                    ])
                    ->setUsedDirectives([
                        '@schemaDirective',
                        '@codeDirective',
                        '@deprecated',
                        '@scalar',
                        '@mock',
                    ]),
                (new TestSettings())
                    ->setDirectiveDefinitionFilter(
                        static function (string $directive, bool $isStandard): bool {
                            return $isStandard === false
                                && !in_array($directive, ['mock', 'scalar', 'codeDirective'], true);
                        },
                    ),
                0,
            ],
            TestSettings::class.' (TypeDefinitionFilter)'      => [
                (new GraphQLExpectedSchema(
                    self::getTestData()->file('~printSchema-TestSettings-TypeDefinitionFilter.graphql'),
                ))
                    ->setUsedTypes([
                        'Boolean',
                        'CodeDirectiveEnum',
                        'CodeDirectiveInput',
                        'CodeDirectiveScalar',
                        'CodeDirectiveScalarCustomClass',
                        'CodeEnum',
                        'CodeInput',
                        'CodeScalar',
                        'CodeType',
                        'CodeUnion',
                        'Query',
                        'SchemaEnum',
                        'SchemaInput',
                        'SchemaInterfaceA',
                        'SchemaInterfaceB',
                        'SchemaScalar',
                        'SchemaType',
                        'SchemaUnion',
                        'String',
                        'CodeInterface',
                    ])
                    ->setUsedDirectives([
                        '@schemaDirective',
                        '@codeDirective',
                        '@deprecated',
                        '@scalar',
                        '@mock',
                    ]),
                (new TestSettings())
                    ->setTypeDefinitionFilter(
                        static function (string $type, bool $isStandard): bool {
                            return $isStandard === false
                                && !str_starts_with($type, 'Code');
                        },
                    ),
                0,
            ],
            TestSettings::class.' (everything)'                => [
                (new GraphQLExpectedSchema(
                    self::getTestData()->file('~printSchema-TestSettings-Everything.graphql'),
                ))
                    ->setUsedTypes([
                        'Int',
                        'Query',
                        'String',
                        'Boolean',
                        'SchemaType',
                        'SchemaEnum',
                        'SchemaInput',
                        'SchemaUnion',
                        'SchemaScalar',
                        'SchemaInterfaceA',
                        'SchemaInterfaceB',
                        'CodeScalar',
                        'CodeInput',
                        'CodeUnion',
                        'CodeEnum',
                        'CodeType',
                        'CodeDirectiveEnum',
                        'CodeDirectiveInput',
                        'CodeDirectiveScalar',
                        'CodeDirectiveScalarCustomClass',
                        'CodeInterface',
                    ])
                    ->setUsedDirectives([
                        '@schemaDirective',
                        '@codeDirective',
                        '@deprecated',
                        '@scalar',
                        '@mock',
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
    public static function dataProviderPrintSchemaType(): array {
        return [
            'CodeUnion'  => [
                (new GraphQLExpectedType(
                    self::getTestData()->file('~printSchemaType-CodeUnion.graphql'),
                ))
                    ->setUsedTypes([
                        'String',
                        'Boolean',
                        'CodeType',
                        'CodeUnion',
                    ])
                    ->setUsedDirectives([
                        '@schemaDirective',
                    ]),
                new TestSettings(),
                1,
                new UnionType([
                    'name'  => 'CodeUnion',
                    'types' => [
                        new ObjectType([
                            'name'   => 'CodeType',
                            'fields' => [
                                'field' => [
                                    'type' => Type::string(),
                                ],
                            ],
                        ]),
                    ],
                ]),
            ],
            'CodeInput'  => [
                (new GraphQLExpectedType(
                    self::getTestData()->file('~printSchemaType-CodeInput.graphql'),
                ))
                    ->setUsedTypes([
                        'String',
                        'Boolean',
                        'CodeInput',
                    ])
                    ->setUsedDirectives([
                        '@schemaDirective',
                    ]),
                new TestSettings(),
                0,
                'CodeInput',
            ],
            'SchemaType' => [
                (new GraphQLExpectedType(
                    self::getTestData()->file('~printSchemaType-SchemaType.graphql'),
                ))
                    ->setUsedTypes([
                        'Boolean',
                        'SchemaInterfaceB',
                        'String',
                        'CodeUnion',
                        'SchemaScalar',
                        'CodeInput',
                        'CodeScalar',
                        'CodeEnum',
                        'SchemaUnion',
                        'SchemaType',
                        'SchemaEnum',
                        'CodeType',
                        'CodeInterface',
                        'SchemaInterfaceA',
                    ])
                    ->setUsedDirectives([
                        // empty
                    ]),
                (new TestSettings())
                    ->setPrintDirectives(false),
                0,
                'SchemaType',
            ],
        ];
    }

    /**
     * @return array<string, array<mixed>>
     */
    public static function dataProviderPrintType(): array {
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
                            'name'   => 'CodeType',
                            'fields' => [
                                'field' => [
                                    'type' => Type::string(),
                                ],
                            ],
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
                        'a' => [
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
