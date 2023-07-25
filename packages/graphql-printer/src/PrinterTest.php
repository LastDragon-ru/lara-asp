<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter;

use Closure;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Argument;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\EnumValueDefinition;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Settings\DefaultSettings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Settings\GraphQLSettings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\GraphQLExpected;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;

use function str_ends_with;

// @phpcs:disable Generic.Files.LineLength.TooLong

/**
 * @internal
 */
#[CoversClass(Printer::class)]
class PrinterTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderPrintSchema
     * @dataProvider dataProviderPrintType
     * @dataProvider dataProviderPrintNode
     *
     * @param Closure(static): ?Schema                                                                                             $schemaFactory
     * @param Closure(static, ?Schema): (Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema) $printableFactory
     * @param Closure(static, ?Schema): ((TypeNode&Node)|Type|null)|null                                                           $typeFactory
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
        $printer   = new Printer($settings, null, $schema);
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
     * @param Closure(static): ?Schema                                                                                             $schemaFactory
     * @param Closure(static, ?Schema): (Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema) $exportableFactory
     * @param Closure(static, ?Schema): ((TypeNode&Node)|Type|null)|null                                                           $typeFactory
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
        $printer    = new Printer($settings, null, $schema);
        $type       = $typeFactory ? $typeFactory($this, $schema) : null;
        $exportable = $exportableFactory($this, $schema);
        $actual     = $printer->export($exportable, $level, $used, $type);

        $this->assertGraphQLPrintableEquals($expected, $actual);
    }

    /**
     * @dataProvider dataProviderPrintSchema
     *
     * @param Closure(static): ?Schema         $schemaFactory
     * @param Closure(static, ?Schema): Schema $printableFactory
     */
    public function testPrintSchema(
        GraphQLExpected $expected,
        ?Settings $settings,
        int $level,
        int $used,
        Closure $schemaFactory,
        Closure $printableFactory,
    ): void {
        $printer   = (new Printer())->setSettings($settings)->setLevel($level);
        $schema    = $schemaFactory($this);
        $printable = $printableFactory($this, $schema);
        $actual    = $printer->printSchema($printable);

        $this->assertGraphQLPrintableEquals($expected, $actual);
    }

    /**
     * @dataProvider dataProviderExportType
     *
     * @param Closure(static): Schema       $schemaFactory
     * @param Closure(static, Schema): Type $typeFactory
     */
    public function testPrintSchemaType(
        GraphQLExpected $expected,
        ?Settings $settings,
        int $level,
        int $used,
        Closure $schemaFactory,
        Closure $typeFactory,
    ): void {
        $printer = (new Printer())->setSettings($settings)->setLevel($level);
        $schema  = $schemaFactory($this);
        $type    = $typeFactory($this, $schema);
        $actual  = $printer->printSchemaType($schema, $type);

        $this->assertGraphQLExportableEquals($expected, $actual);
    }

    /**
     * @dataProvider dataProviderPrintType
     *
     * @param Closure(static): ?Schema       $schemaFactory
     * @param Closure(static, ?Schema): Type $typeFactory
     */
    public function testPrintType(
        GraphQLExpected $expected,
        ?Settings $settings,
        int $level,
        int $used,
        Closure $schemaFactory,
        Closure $typeFactory,
    ): void {
        $printer = (new Printer())->setSettings($settings)->setLevel($level);
        $schema  = $schemaFactory($this);
        $type    = $typeFactory($this, $schema);
        $actual  = $printer->printType($type);

        $this->assertGraphQLPrintableEquals($expected, $actual);
    }

    /**
     * @dataProvider dataProviderPrintNode
     *
     * @param Closure(static): ?Schema       $schemaFactory
     * @param Closure(static, ?Schema): Node $nodeFactory
     */
    public function testPrintNode(
        GraphQLExpected $expected,
        ?Settings $settings,
        int $level,
        int $used,
        Closure $schemaFactory,
        Closure $nodeFactory,
    ): void {
        $printer = (new Printer())->setSettings($settings)->setLevel($level);
        $schema  = $schemaFactory($this);
        $node    = $nodeFactory($this, $schema);
        $actual  = $printer->printNode($node);

        $this->assertGraphQLPrintableEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array<mixed>>
     */
    public static function dataProviderPrintSchema(): array {
        $schemaFactory    = static function (): Schema {
            return BuildSchema::build(self::getTestData()->content('~schema.graphql'));
        };
        $printableFactory = static function (TestCase $test, ?Schema $schema): ?Schema {
            return $schema;
        };

        return [
            'Schema'                                        => [
                new GraphQLExpected(
                    self::getTestData()->file('~print-Schema-DefaultSettings.graphql'),
                ),
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
                        'Enum',
                        'Int',
                        'Float',
                        'InputA',
                        'InterfaceA',
                        'InterfaceB',
                        'InterfaceC',
                        'Scalar',
                        'TypeB',
                        'Mutation',
                        'TypeA',
                        'Union',
                        'TypeC',
                        'Subscription',
                        'InputHidden',
                        'TypeHidden',
                    ])
                    ->setUsedDirectives([
                        '@deprecated',
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
                        'InputHidden',
                        'TypeHidden',
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
                        'Enum',
                        'Float',
                        'InputA',
                        'InterfaceA',
                        'InterfaceB',
                        'InterfaceC',
                        'Scalar',
                        'TypeB',
                        'Mutation',
                        'TypeA',
                        'Union',
                        'TypeC',
                        'Subscription',
                        'InputHidden',
                        'TypeHidden',
                    ])
                    ->setUsedDirectives([
                        '@deprecated',
                        '@directive',
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
                        'Enum',
                        'Int',
                        'Float',
                        'InputA',
                        'InterfaceA',
                        'InterfaceB',
                        'InterfaceC',
                        'Scalar',
                        'TypeB',
                        'Mutation',
                        'TypeA',
                        'Union',
                        'TypeC',
                        'Subscription',
                        'InputHidden',
                        'TypeHidden',
                    ])
                    ->setUsedDirectives([
                        '@deprecated',
                        '@directive',
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
                        'InterfaceA',
                        'InterfaceB',
                        'InterfaceC',
                        'InputHidden',
                        'TypeHidden',
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
                        'Enum',
                        'Int',
                        'Float',
                        'InputA',
                        'InterfaceA',
                        'InterfaceB',
                        'InterfaceC',
                        'Scalar',
                        'TypeB',
                        'Mutation',
                        'TypeA',
                        'Union',
                        'TypeC',
                        'Subscription',
                        'InputHidden',
                        'TypeHidden',
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
                0,
                $schemaFactory,
                $printableFactory,
            ],
            'Schema-TestSettings-TypeDefinitionFilter'      => [
                (new GraphQLExpected(
                    self::getTestData()->file('~print-Schema-TestSettings-TypeDefinitionFilter.graphql'),
                ))
                    ->setUsedTypes([
                        'Query',
                        'String',
                        'Enum',
                        'Int',
                        'Float',
                        'InputA',
                        'InterfaceA',
                        'InterfaceB',
                        'InterfaceC',
                        'Scalar',
                        'TypeB',
                        'Mutation',
                        'TypeA',
                        'Union',
                        'TypeC',
                        'InputHidden',
                        'TypeHidden',
                        'Subscription',
                    ])
                    ->setUsedDirectives([
                        '@deprecated',
                        '@directive',
                    ]),
                (new TestSettings())
                    ->setTypeDefinitionFilter(
                        static function (string $type, bool $isStandard): bool {
                            return $isStandard === false
                                && $type !== 'Subscription';
                        },
                    ),
                0,
                0,
                $schemaFactory,
                $printableFactory,
            ],
            'Schema-TestSettings-TypeFilter'                => [
                (new GraphQLExpected(
                    self::getTestData()->file('~print-Schema-TestSettings-TypeFilter.graphql'),
                ))
                    ->setUsedTypes([
                        'Query',
                        'String',
                        'Enum',
                        'Int',
                        'Float',
                        'InputA',
                        'InterfaceA',
                        'InterfaceB',
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
                    ->setTypeFilter(
                        static function (string $type, bool $isStandard): bool {
                            return !str_ends_with($type, 'Hidden');
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
                        'Enum',
                        'Int',
                        'Float',
                        'InputA',
                        'InterfaceA',
                        'InterfaceB',
                        'InterfaceC',
                        'Scalar',
                        'TypeB',
                        'Mutation',
                        'TypeA',
                        'Union',
                        'TypeC',
                        'Subscription',
                        'String',
                        'InputHidden',
                        'TypeHidden',
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
                0,
                $schemaFactory,
                $printableFactory,
            ],
        ];
    }

    /**
     * @return array<string, array<mixed>>
     */
    public static function dataProviderExportType(): array {
        $schemaFactory = static function (): Schema {
            return BuildSchema::build(self::getTestData()->content('~schema.graphql'));
        };

        return [
            'UnionType'     => [
                (new GraphQLExpected(
                    self::getTestData()->file('~export-UnionType.graphql'),
                ))
                    ->setUsedTypes([
                        'String',
                        'InterfaceA',
                        'InterfaceB',
                        'InterfaceC',
                        'Int',
                        'Float',
                        'TypeA',
                        'Union',
                        'InputHidden',
                        'TypeHidden',
                    ])
                    ->setUsedDirectives([
                        '@deprecated',
                        '@directive',
                    ]),
                new TestSettings(),
                0,
                0,
                $schemaFactory,
                static function (): Type {
                    return new UnionType([
                        'name'  => 'Union',
                        'types' => [
                            new ObjectType([
                                'name'   => 'TypeA',
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
            'ObjectType'    => [
                (new GraphQLExpected(
                    self::getTestData()->file('~export-ObjectType.graphql'),
                ))
                    ->setUsedTypes([
                        'String',
                        'InterfaceC',
                        'Int',
                        'Float',
                        'TypeA',
                        'InterfaceA',
                        'InterfaceB',
                        'InputHidden',
                        'TypeHidden',
                    ])
                    ->setUsedDirectives([
                        '@deprecated',
                        '@directive',
                    ]),
                new TestSettings(),
                0,
                0,
                $schemaFactory,
                static function (TestCase $test, Schema $schema): Type {
                    $type = $schema->getType('TypeA');

                    self::assertNotNull($type);

                    return $type;
                },
            ],
            'InterfaceType' => [
                (new GraphQLExpected(
                    self::getTestData()->file('~export-InterfaceType.graphql'),
                ))
                    ->setUsedTypes([
                        'String',
                        'Int',
                        'Float',
                        'InterfaceA',
                        'InterfaceB',
                        'InterfaceC',
                        'InputHidden',
                        'TypeHidden',
                    ])
                    ->setUsedDirectives([
                        '@directive',
                    ]),
                new TestSettings(),
                1,
                0,
                $schemaFactory,
                static function (TestCase $test, Schema $schema): Type {
                    $type = $schema->getType('InterfaceC');

                    self::assertNotNull($type);

                    return $type;
                },
            ],
        ];
    }

    /**
     * @return array<string, array<mixed>>
     */
    public static function dataProviderPrintType(): array {
        $schemaFactory = static function (): ?Schema {
            return null;
        };

        return [
            'UnionType'       => [
                (new GraphQLExpected(
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
                0,
                $schemaFactory,
                static function (): Type {
                    return new InputObjectType([
                        'name'        => 'CodeInput',
                        'astNode'     => Parser::inputObjectTypeDefinition('input InputObjectType @schemaDirective'),
                        'description' => 'Description',
                        'fields'      => [
                            [
                                'name' => 'a',
                                'type' => Type::boolean(),
                            ],
                        ],
                    ]);
                },
            ],
            'InterfaceType'   => [
                (new GraphQLExpected(
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
     * @return array<string, array<mixed>>
     */
    public static function dataProviderPrintNode(): array {
        $schemaFactory = static function (): ?Schema {
            return null;
        };

        return [
            'UnionTypeDefinitionNode'   => [
                (new GraphQLExpected(
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
     * @return array<string, array<mixed>>
     */
    public static function dataProviderExportNode(): array {
        $schemaFactory = static function (): Schema {
            return BuildSchema::build(self::getTestData()->content('~schema.graphql'));
        };

        return [
            'UnionTypeDefinitionNode'   => [
                (new GraphQLExpected(
                    self::getTestData()->file('~export-UnionTypeDefinitionNode.graphql'),
                ))
                    ->setUsedTypes([
                        'String',
                        'InterfaceA',
                        'InterfaceB',
                        'InterfaceC',
                        'Int',
                        'Float',
                        'TypeA',
                        'Union',
                        'InputHidden',
                        'TypeHidden',
                    ])
                    ->setUsedDirectives([
                        '@deprecated',
                        '@directive',
                    ]),
                new TestSettings(),
                1,
                0,
                $schemaFactory,
                static function (): Node {
                    return Parser::unionTypeDefinition(
                        'union Union = TypeA',
                    );
                },
            ],
            'InputObjectTypeDefinition' => [
                (new GraphQLExpected(
                    self::getTestData()->file('~export-InputObjectTypeDefinition.graphql'),
                ))
                    ->setUsedTypes([
                        'String',
                        'Int',
                        'Float',
                        'InputUnused',
                        'InputA',
                        'InputHidden',
                    ])
                    ->setUsedDirectives([
                        '@directive',
                    ]),
                new TestSettings(),
                0,
                0,
                $schemaFactory,
                static function (): Node {
                    return Parser::inputObjectTypeDefinition(
                        '"Description" input InputUnused { a: InputA }',
                    );
                },
            ],
        ];
    }
    // </editor-fold>
}
