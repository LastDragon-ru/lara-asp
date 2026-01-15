<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter;

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
use LastDragon_ru\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\GraphQLPrinter\Package\TestCase;
use LastDragon_ru\GraphQLPrinter\Settings\DefaultSettings;
use LastDragon_ru\GraphQLPrinter\Settings\GraphQLSettings;
use LastDragon_ru\PhpUnit\GraphQL\Expected;
use LastDragon_ru\PhpUnit\GraphQL\PrinterSettings;
use LastDragon_ru\PhpUnit\Utils\TestData;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function str_ends_with;

/**
 * @internal
 */
#[CoversClass(Printer::class)]
final class PrinterTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param Closure(static): ?Schema                                                                                             $schemaFactory
     * @param Closure(static, ?Schema): (Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema) $printableFactory
     * @param Closure(static, ?Schema): ((TypeNode&Node)|Type|null)|null                                                           $typeFactory
     */
    #[DataProvider('dataProviderPrintSchema')]
    #[DataProvider('dataProviderPrint')]
    public function testPrint(
        Expected $expected,
        ?Settings $settings,
        int $level,
        int $used,
        Closure $schemaFactory,
        Closure $printableFactory,
        ?Closure $typeFactory = null,
    ): void {
        $schema    = $schemaFactory($this);
        $printer   = new Printer($settings, null, $schema);
        $type      = $typeFactory !== null ? $typeFactory($this, $schema) : null;
        $printable = $printableFactory($this, $schema);
        $actual    = $printer->print($printable, $level, $used, $type);

        $this->assertGraphQLPrintableEquals($expected, $actual);

        if ($printable instanceof Schema) {
            $printable->assertValid();
        }
    }

    /**
     * @param Closure(static): ?Schema                                                                                             $schemaFactory
     * @param Closure(static, ?Schema): (Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema) $exportableFactory
     * @param Closure(static, ?Schema): ((TypeNode&Node)|Type|null)|null                                                           $typeFactory
     */
    #[DataProvider('dataProviderPrintSchema')]
    #[DataProvider('dataProviderExport')]
    public function testExport(
        Expected $expected,
        ?Settings $settings,
        int $level,
        int $used,
        Closure $schemaFactory,
        Closure $exportableFactory,
        ?Closure $typeFactory = null,
    ): void {
        $schema     = $schemaFactory($this);
        $printer    = new Printer($settings, null, $schema);
        $type       = $typeFactory !== null ? $typeFactory($this, $schema) : null;
        $exportable = $exportableFactory($this, $schema);
        $actual     = $printer->export($exportable, $level, $used, $type);

        $this->assertGraphQLPrintableEquals($expected, $actual);

        if ($exportable instanceof Schema) {
            $exportable->assertValid();
        }
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array<array-key, mixed>>
     */
    public static function dataProviderPrintSchema(): array {
        $data             = TestData::get();
        $schemaFactory    = static function () use ($data): Schema {
            return BuildSchema::build($data->content('schema.graphql'));
        };
        $printableFactory = static function (TestCase $test, ?Schema $schema): ?Schema {
            return $schema;
        };

        return [
            'null'                                      => [
                new Expected(
                    $data->content('print/DefaultSettings.graphql'),
                ),
                null,
                0,
                0,
                $schemaFactory,
                $printableFactory,
            ],
            'DefaultSettings'                           => [
                new Expected(
                    value     : $data->content('print/DefaultSettings.graphql'),
                    types     : [
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
                    ],
                    directives: [
                        '@deprecated',
                        '@directive',
                    ],
                ),
                new DefaultSettings(),
                0,
                0,
                $schemaFactory,
                $printableFactory,
            ],
            'GraphQLSettings'                           => [
                new Expected(
                    value     : $data->content('print/GraphQLSettings.graphql'),
                    types     : [
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
                    ],
                    directives: [
                        '@deprecated',
                    ],
                ),
                new GraphQLSettings(),
                0,
                0,
                $schemaFactory,
                $printableFactory,
            ],
            'PrinterSettings'                           => [
                new Expected(
                    value     : $data->content('print/PrinterSettings.graphql'),
                    types     : [
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
                    ],
                    directives: [
                        '@deprecated',
                        '@directive',
                    ],
                ),
                new PrinterSettings(),
                0,
                0,
                $schemaFactory,
                $printableFactory,
            ],
            'PrinterSettings-NoDirectivesDefinitions'   => [
                new Expected(
                    value     : $data->content('print/PrinterSettings-NoDirectivesDefinitions.graphql'),
                    types     : [
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
                    ],
                    directives: [
                        '@deprecated',
                        '@directive',
                    ],
                ),
                (new PrinterSettings())
                    ->setPrintDirectiveDefinitions(false),
                0,
                0,
                $schemaFactory,
                $printableFactory,
            ],
            'PrinterSettings-NoNormalization'           => [
                new Expected(
                    value     : $data->content('print/PrinterSettings-NoNormalization.graphql'),
                    types     : [
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
                    ],
                    directives: [
                        '@deprecated',
                        '@directive',
                    ],
                ),
                (new PrinterSettings())
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
            'PrinterSettings-DirectiveDefinitionFilter' => [
                new Expected(
                    value     : $data->content('print/PrinterSettings-DirectiveDefinitionFilter.graphql'),
                    types     : [
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
                    ],
                    directives: [
                        '@deprecated',
                        '@directive',
                    ],
                ),
                (new PrinterSettings())
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
            'PrinterSettings-TypeDefinitionFilter'      => [
                new Expected(
                    value     : $data->content('print/PrinterSettings-TypeDefinitionFilter.graphql'),
                    types     : [
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
                    ],
                    directives: [
                        '@deprecated',
                        '@directive',
                    ],
                ),
                (new PrinterSettings())
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
            'PrinterSettings-TypeFilter'                => [
                new Expected(
                    value     : $data->content('print/PrinterSettings-TypeFilter.graphql'),
                    types     : [
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
                    ],
                    directives: [
                        '@deprecated',
                        '@directive',
                    ],
                ),
                (new PrinterSettings())
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
            'PrinterSettings-Everything'                => [
                new Expected(
                    value     : $data->content('print/PrinterSettings-Everything.graphql'),
                    types     : [
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
                    ],
                    directives: [
                        '@deprecated',
                        '@directive',
                    ],
                ),
                (new PrinterSettings())
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
    public static function dataProviderPrint(): array {
        $schemaFactory = static function (): ?Schema {
            return null;
        };

        return [
            'UnionType'                                   => [
                new Expected(
                    value: <<<'GRAPHQL'
                            union CodeUnion =
                                | CodeType
                        GRAPHQL,
                    types: [
                        'CodeType',
                        'CodeUnion',
                    ],
                ),
                new PrinterSettings(),
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
            'InputObjectType'                             => [
                new Expected(
                    value     : <<<'GRAPHQL'
                        """
                        Description
                        """
                        input CodeInput
                        @schemaDirective
                        {
                            a: Boolean
                        }
                        GRAPHQL,
                    types     : [
                        'Boolean',
                        'CodeInput',
                    ],
                    directives: [
                        '@schemaDirective',
                    ],
                ),
                new PrinterSettings(),
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
            'InterfaceType'                               => [
                new Expected(
                    value     : <<<'GRAPHQL'
                        """
                        Description
                        """
                        interface CodeInterface {
                            a: Boolean!
                        }
                        GRAPHQL,
                    types     : [
                        'Boolean',
                        'CodeInterface',
                    ],
                    directives: [
                        // empty
                    ],
                ),
                (new PrinterSettings())
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
            'UnionTypeDefinitionNode'                     => [
                new Expected(
                    value: <<<'GRAPHQL'
                        union CodeUnion =
                            | CodeType
                    GRAPHQL,
                    types: [
                        'CodeType',
                        'CodeUnion',
                    ],
                ),
                new PrinterSettings(),
                1,
                0,
                $schemaFactory,
                static function (): Node {
                    return Parser::unionTypeDefinition(
                        'union CodeUnion = CodeType',
                    );
                },
            ],
            'InputObjectTypeDefinition'                   => [
                new Expected(
                    value     : <<<'GRAPHQL'
                        """
                        Description
                        """
                        input CodeInput
                        @schemaDirective
                        {
                            a: Boolean
                        }
                        GRAPHQL,
                    types     : [
                        'Boolean',
                        'CodeInput',
                    ],
                    directives: [
                        '@schemaDirective',
                    ],
                ),
                new PrinterSettings(),
                0,
                0,
                $schemaFactory,
                static function (): Node {
                    return Parser::inputObjectTypeDefinition(
                        '"Description" input CodeInput @schemaDirective { a: Boolean }',
                    );
                },
            ],
            'DirectiveNode (forbidden)'                   => [
                new Expected(''),
                (new PrinterSettings())
                    ->setPrintDirectiveDefinitions(true)
                    ->setPrintDirectives(true)
                    ->setDirectiveDefinitionFilter(static fn () => false)
                    ->setDirectiveFilter(static fn () => false),
                0,
                0,
                $schemaFactory,
                static function (): Node {
                    return Parser::directive(
                        '@test',
                    );
                },
            ],
            'DirectiveNode (printing disabled)'           => [
                new Expected(
                    value     : <<<'GRAPHQL'
                        @test
                        GRAPHQL,
                    directives: [
                        '@test',
                    ],
                ),
                (new PrinterSettings())
                    ->setPrintDirectiveDefinitions(false)
                    ->setPrintDirectives(false),
                0,
                0,
                $schemaFactory,
                static function (): Node {
                    return Parser::directive(
                        '@test',
                    );
                },
            ],
            'DirectiveDefinitionNode (forbidden)'         => [
                new Expected(''),
                (new PrinterSettings())
                    ->setPrintDirectiveDefinitions(true)
                    ->setPrintDirectives(true)
                    ->setDirectiveDefinitionFilter(static fn () => false)
                    ->setDirectiveFilter(static fn () => false),
                0,
                0,
                $schemaFactory,
                static function (): Node {
                    return Parser::directiveDefinition(
                        '"Description" directive @test on SCALAR',
                    );
                },
            ],
            'DirectiveDefinitionNode (printing disabled)' => [
                new Expected(
                    value     : <<<'GRAPHQL'
                        """
                        Description
                        """
                        directive @test
                        on
                            | SCALAR
                        GRAPHQL,
                    directives: [
                        '@test',
                    ],
                ),
                (new PrinterSettings())
                    ->setPrintDirectiveDefinitions(false)
                    ->setPrintDirectives(false),
                0,
                0,
                $schemaFactory,
                static function (): Node {
                    return Parser::directiveDefinition(
                        '"Description" directive @test on SCALAR',
                    );
                },
            ],
            'ScalarTypeDefinitionNode (forbidden)'        => [
                new Expected(''),
                (new PrinterSettings())
                    ->setTypeDefinitionFilter(static fn () => false)
                    ->setTypeFilter(static fn () => false),
                0,
                0,
                $schemaFactory,
                static function (): Node {
                    return Parser::scalarTypeDefinition(
                        '"Description" scalar test',
                    );
                },
            ],
        ];
    }

    /**
     * @return array<string, array<array-key, mixed>>
     */
    public static function dataProviderExport(): array {
        $data          = TestData::get();
        $schemaFactory = static function () use ($data): Schema {
            return BuildSchema::build($data->content('schema.graphql'));
        };

        return [
            'UnionType'                 => [
                new Expected(
                    value     : $data->content('export/UnionType.graphql'),
                    types     : [
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
                    ],
                    directives: [
                        '@deprecated',
                        '@directive',
                    ],
                ),
                new PrinterSettings(),
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
            'ObjectType'                => [
                new Expected(
                    value     : $data->content('export/ObjectType.graphql'),
                    types     : [
                        'String',
                        'InterfaceC',
                        'Int',
                        'Float',
                        'TypeA',
                        'InterfaceA',
                        'InterfaceB',
                        'InputHidden',
                        'TypeHidden',
                    ],
                    directives: [
                        '@deprecated',
                        '@directive',
                    ],
                ),
                new PrinterSettings(),
                0,
                0,
                $schemaFactory,
                static function (TestCase $test, Schema $schema): Type {
                    $type = $schema->getType('TypeA');

                    self::assertNotNull($type);

                    return $type;
                },
            ],
            'InterfaceType'             => [
                new Expected(
                    value     : $data->content('export/InterfaceType.graphql'),
                    types     : [
                        'String',
                        'Int',
                        'Float',
                        'InterfaceA',
                        'InterfaceC',
                        'InputHidden',
                        'TypeHidden',
                    ],
                    directives: [
                        '@directive',
                    ],
                ),
                new PrinterSettings(),
                1,
                0,
                $schemaFactory,
                static function (TestCase $test, Schema $schema): Type {
                    $type = $schema->getType('InterfaceC');

                    self::assertNotNull($type);

                    return $type;
                },
            ],
            'UnionTypeDefinitionNode'   => [
                new Expected(
                    value     : $data->content('export/UnionTypeDefinitionNode.graphql'),
                    types     : [
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
                    ],
                    directives: [
                        '@deprecated',
                        '@directive',
                    ],
                ),
                new PrinterSettings(),
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
                new Expected(
                    value     : $data->content('export/InputObjectTypeDefinition.graphql'),
                    types     : [
                        'String',
                        'Int',
                        'Float',
                        'InputUnused',
                        'InputA',
                        'InputHidden',
                    ],
                    directives: [
                        '@directive',
                    ],
                ),
                new PrinterSettings(),
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
