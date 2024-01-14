<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Printer;

use Closure;
use Exception;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Argument;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\EnumValueDefinition;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType;
use GraphQL\Type\Schema;
use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Printer;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Settings\DefaultSettings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Settings\GraphQLSettings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\GraphQLExpected;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\TestSettings;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use SplFileInfo;

use function in_array;
use function str_starts_with;

// @phpcs:disable Generic.Files.LineLength.TooLong

/**
 * @internal
 */
#[CoversClass(Printer::class)]
final class PrinterTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderPrintSchema
     * @dataProvider dataProviderPrintType
     * @dataProvider dataProviderPrintNode
     *
     * @param Closure(static): (Schema|SplFileInfo|string)                                                                        $schemaFactory
     * @param Closure(static, Schema): (Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema) $printableFactory
     * @param Closure(static, Schema): ((TypeNode&Node)|Type|null)|null                                                           $typeFactory
     */
    public function testPrint(
        GraphQLExpected $expected,
        ?Settings $settings,
        int $level,
        int $used,
        Closure $schemaFactory,
        Closure $printableFactory,
        Closure $typeFactory = null,
    ): void {
        $schema    = $schemaFactory($this);
        $schema    = $this->useGraphQLSchema($schema)->getGraphQLSchema();
        $printer   = Container::getInstance()->make(Printer::class)->setSettings($settings);
        $type      = $typeFactory ? $typeFactory($this, $schema) : null;
        $printable = $printableFactory($this, $schema);
        $actual    = $printer->print($printable, $level, $used, $type);

        $this->assertGraphQLPrintableEquals($expected, $actual);
    }

    /**
     * @dataProvider dataProviderPrintSchema
     * @dataProvider dataProviderExportType
     * @dataProvider dataProviderExportNode
     *
     * @param Closure(static): (Schema|SplFileInfo|string)                                                                        $schemaFactory
     * @param Closure(static, Schema): (Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema) $exportableFactory
     * @param Closure(static, Schema): ((TypeNode&Node)|Type|null)|null                                                           $typeFactory
     */
    public function testExport(
        GraphQLExpected $expected,
        ?Settings $settings,
        int $level,
        int $used,
        Closure $schemaFactory,
        Closure $exportableFactory,
        Closure $typeFactory = null,
    ): void {
        $schema     = $schemaFactory($this);
        $schema     = $this->useGraphQLSchema($schema)->getGraphQLSchema();
        $printer    = Container::getInstance()->make(Printer::class)->setSettings($settings);
        $type       = $typeFactory ? $typeFactory($this, $schema) : null;
        $exportable = $exportableFactory($this, $schema);
        $actual     = $printer->export($exportable, $level, $used, $type);

        $this->assertGraphQLPrintableEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array<array-key, mixed>>
     */
    public static function dataProviderPrintSchema(): array {
        $schemaFactory    = self::getSchemaFactory();
        $printableFactory = static function (TestCase $test, ?Schema $schema): ?Schema {
            return $schema;
        };

        return [
            'Schema'                                        => [
                (new GraphQLExpected(
                    self::getTestData()->file('~print-Schema-DefaultSettings.graphql'),
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
                        'CodeDirectiveScalarCustomClass',
                        'CodeDirectiveEnum',
                        'CodeDirectiveInput',
                        'CodeDirectiveScalar',
                        'Int',
                    ])
                    ->setUsedDirectives([
                        '@deprecated',
                        '@codeDirective',
                        '@mock',
                        '@schemaDirective',
                        '@scalar',
                    ]),
                null,
                0,
                0,
                $schemaFactory,
                $printableFactory,
            ],
            'Schema-DefaultSettings'                        => [
                (new GraphQLExpected(
                    self::getTestData()->file('~print-Schema-DefaultSettings.graphql'),
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
                        'CodeDirectiveScalarCustomClass',
                        'CodeDirectiveEnum',
                        'CodeDirectiveInput',
                        'CodeDirectiveScalar',
                        'Int',
                    ])
                    ->setUsedDirectives([
                        '@deprecated',
                        '@codeDirective',
                        '@mock',
                        '@schemaDirective',
                        '@scalar',
                    ]),
                new DefaultSettings(),
                0,
                0,
                $schemaFactory,
                $printableFactory,
            ],
            'Schema-GraphQLSettings'                        => [
                (new GraphQLExpected(
                    self::getTestData()->file('~print-Schema-GraphQLSettings.graphql'),
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
                0,
                $schemaFactory,
                $printableFactory,
            ],
            'Schema-TestSettings'                           => [
                (new GraphQLExpected(
                    self::getTestData()->file('~print-Schema-TestSettings.graphql'),
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
                0,
                $schemaFactory,
                $printableFactory,
            ],
            'Schema-TestSettings-NoDirectivesDefinitions'   => [
                (new GraphQLExpected(
                    self::getTestData()->file('~print-Schema-TestSettings-NoDirectivesDefinitions.graphql'),
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
                0,
                $schemaFactory,
                $printableFactory,
            ],
            'Schema-TestSettings-NoNormalization'           => [
                (new GraphQLExpected(
                    self::getTestData()->file('~print-Schema-TestSettings-NoNormalization.graphql'),
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
                    ->setNormalizeDefinitions(false)
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
                0,
                $schemaFactory,
                $printableFactory,
            ],
            'Schema-TestSettings-DirectiveDefinitionFilter' => [
                (new GraphQLExpected(
                    self::getTestData()->file('~print-Schema-TestSettings-DirectiveDefinitionFilter.graphql'),
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
                0,
                $schemaFactory,
                $printableFactory,
            ],
            'Schema-TestSettings-TypeDefinitionFilter'      => [
                (new GraphQLExpected(
                    self::getTestData()->file('~print-Schema-TestSettings-TypeDefinitionFilter.graphql'),
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
                0,
                $schemaFactory,
                $printableFactory,
            ],
            'Schema-TestSettings-Everything'                => [
                (new GraphQLExpected(
                    self::getTestData()->file('~print-Schema-TestSettings-Everything.graphql'),
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
                0,
                $schemaFactory,
                $printableFactory,
            ],
        ];
    }

    /**
     * @return array<string, array<array-key, mixed>>
     */
    public static function dataProviderExportType(): array {
        $schemaFactory = self::getSchemaFactory();

        return [
            'CodeUnion'  => [
                (new GraphQLExpected(
                    self::getTestData()->file('~export-CodeUnion.graphql'),
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
                0,
                $schemaFactory,
                static function (): Type {
                    return new UnionType([
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
                    ]);
                },
            ],
            'CodeInput'  => [
                (new GraphQLExpected(
                    self::getTestData()->file('~export-CodeInput.graphql'),
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
                0,
                $schemaFactory,
                static function (TestCase $test, Schema $schema): Type {
                    $type = $schema->getType('CodeInput');

                    self::assertNotNull($type);

                    return $type;
                },
            ],
            'SchemaType' => [
                (new GraphQLExpected(
                    self::getTestData()->file('~export-SchemaType.graphql'),
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
                0,
                $schemaFactory,
                static function (TestCase $test, Schema $schema): Type {
                    $type = $schema->getType('SchemaType');

                    self::assertNotNull($type);

                    return $type;
                },
            ],
        ];
    }

    /**
     * @return array<string, array<array-key, mixed>>
     */
    public static function dataProviderPrintType(): array {
        $schemaFactory = self::getSchemaFactory();

        return [
            'UnionType'       => [
                (new GraphQLExpected(
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
                0,
                $schemaFactory,
                static function (): Type {
                    return new UnionType([
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
                    ]);
                },
            ],
            'InputObjectType' => [
                (new GraphQLExpected(
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
                0,
                $schemaFactory,
                static function (): Type {
                    return new InputObjectType([
                        'name'        => 'CodeInput',
                        'astNode'     => Parser::inputObjectTypeDefinition('input InputObjectType @schemaDirective'),
                        'description' => 'Description',
                        'fields'      => [
                            'a' => [
                                'type' => Type::boolean(),
                            ],
                        ],
                    ]);
                },
            ],
            'InterfaceType'   => [
                (new GraphQLExpected(
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
                0,
                $schemaFactory,
                static function (): Type {
                    return new InterfaceType([
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
                },
            ],
        ];
    }

    /**
     * @return array<string, array<array-key, mixed>>
     */
    public static function dataProviderPrintNode(): array {
        $schemaFactory = self::getSchemaFactory();

        return [
            'UnionTypeDefinitionNode'   => [
                (new GraphQLExpected(
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
                0,
                $schemaFactory,
                static function (): Node {
                    return Parser::unionTypeDefinition(
                        'union CodeUnion = CodeType',
                    );
                },
            ],
            'InputObjectTypeDefinition' => [
                (new GraphQLExpected(
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
                0,
                $schemaFactory,
                static function (): Node {
                    return Parser::inputObjectTypeDefinition(
                        '"Description" input CodeInput @schemaDirective { a: Boolean }',
                    );
                },
            ],
        ];
    }

    /**
     * @return array<string, array<array-key, mixed>>
     */
    public static function dataProviderExportNode(): array {
        $schemaFactory = self::getSchemaFactory();

        return [
            'UnionTypeDefinitionNode'   => [
                (new GraphQLExpected(
                    self::getTestData()->file('~export-UnionTypeDefinitionNode.graphql'),
                ))
                    ->setUsedTypes([
                        'String',
                        'CodeType',
                        'SchemaUnion',
                        'Boolean',
                    ])
                    ->setUsedDirectives([
                        '@schemaDirective',
                    ]),
                new TestSettings(),
                1,
                0,
                $schemaFactory,
                static function (): Node {
                    return Parser::unionTypeDefinition(
                        'union SchemaUnion = CodeType',
                    );
                },
            ],
            'InputObjectTypeDefinition' => [
                (new GraphQLExpected(
                    self::getTestData()->file('~export-InputObjectTypeDefinition.graphql'),
                ))
                    ->setUsedTypes([
                        'String',
                        'CodeInput',
                        'SchemaInput',
                        'Boolean',
                    ])
                    ->setUsedDirectives([
                        '@schemaDirective',
                    ]),
                new TestSettings(),
                0,
                0,
                $schemaFactory,
                static function (): Node {
                    return Parser::inputObjectTypeDefinition(
                        '"Description" input SchemaInput { a: CodeInput }',
                    );
                },
            ],
        ];
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    /**
     * @return Closure(static): SplFileInfo
     */
    private static function getSchemaFactory(): Closure {
        return static function (TestCase $test): SplFileInfo {
            // Types
            $directives = Container::getInstance()->make(DirectiveLocator::class);
            $registry   = Container::getInstance()->make(TypeRegistry::class);
            $directive  = (new class() extends BaseDirective {
                #[Override]
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
                    #[Override]
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
            return self::getTestData()->file('~schema.graphql');
        };
    }
    // </editor-fold>
}
