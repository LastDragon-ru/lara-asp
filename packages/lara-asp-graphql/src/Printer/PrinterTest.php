<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Printer;

use Closure;
use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
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
use LastDragon_ru\GraphQLPrinter\Contracts\Printer;
use LastDragon_ru\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\GraphQLPrinter\Settings\DefaultSettings;
use LastDragon_ru\GraphQLPrinter\Settings\GraphQLSettings;
use LastDragon_ru\LaraASP\GraphQL\Package\TestCase;
use LastDragon_ru\PhpUnit\GraphQL\Expected;
use LastDragon_ru\PhpUnit\GraphQL\PrinterSettings;
use LastDragon_ru\PhpUnit\Utils\TestData;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function in_array;
use function str_replace;
use function str_starts_with;

/**
 * @internal
 */
#[CoversClass(Printer::class)]
final class PrinterTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param Closure(static): (Schema|string)                                                                                    $schemaFactory
     * @param Closure(static, Schema): (Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema) $printableFactory
     * @param Closure(static, Schema): ((TypeNode&Node)|Type|null)|null                                                           $typeFactory
     */
    #[DataProvider('dataProviderPrintSchema')]
    #[DataProvider('dataProviderPrintType')]
    #[DataProvider('dataProviderPrintNode')]
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
        $schema    = $this->useGraphQLSchema($schema)->getGraphQLSchema();
        $printer   = $this->app()->make(Printer::class)->setSettings($settings);
        $type      = $typeFactory !== null ? $typeFactory($this, $schema) : null;
        $printable = $printableFactory($this, $schema);
        $actual    = $printer->print($printable, $level, $used, $type);

        $this->assertGraphQLPrintableEquals($expected, $actual);
    }

    /**
     * @param Closure(static): (Schema|string)                                                                                    $schemaFactory
     * @param Closure(static, Schema): (Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema) $exportableFactory
     * @param Closure(static, Schema): ((TypeNode&Node)|Type|null)|null                                                           $typeFactory
     */
    #[DataProvider('dataProviderPrintSchema')]
    #[DataProvider('dataProviderExportType')]
    #[DataProvider('dataProviderExportNode')]
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
        $schema     = $this->useGraphQLSchema($schema)->getGraphQLSchema();
        $printer    = $this->app()->make(Printer::class)->setSettings($settings);
        $type       = $typeFactory !== null ? $typeFactory($this, $schema) : null;
        $exportable = $exportableFactory($this, $schema);
        $actual     = $printer->export($exportable, $level, $used, $type);

        $this->assertGraphQLPrintableEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{
     *      Expected,
     *      ?Settings,
     *      int,
     *      int,
     *      Closure(static): (Schema|string),
     *      Closure(static, Schema): (Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema),
     *      Closure(static, Schema): ((TypeNode&Node)|Type|null)|null,
     *      }>
     */
    public static function dataProviderPrintSchema(): array {
        $data             = TestData::get();
        $schemaFactory    = self::getSchemaFactory($data);
        $printableFactory = static function (TestCase $test, Schema $schema): Schema {
            return $schema;
        };

        return [
            'Schema'                                           => [
                new Expected(
                    value     : self::getSchemaContent($data, 'print/DefaultSettings.graphql'),
                    types     : [
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
                    ],
                    directives: [
                        '@deprecated',
                        '@codeDirective',
                        '@mock',
                        '@schemaDirective',
                        '@scalar',
                    ],
                ),
                null,
                0,
                0,
                $schemaFactory,
                $printableFactory,
                null,
            ],
            'Schema-DefaultSettings'                           => [
                new Expected(
                    value     : self::getSchemaContent($data, 'print/DefaultSettings.graphql'),
                    types     : [
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
                    ],
                    directives: [
                        '@deprecated',
                        '@codeDirective',
                        '@mock',
                        '@schemaDirective',
                        '@scalar',
                    ],
                ),
                new DefaultSettings(),
                0,
                0,
                $schemaFactory,
                $printableFactory,
                null,
            ],
            'Schema-GraphQLSettings'                           => [
                new Expected(
                    value     : self::getSchemaContent($data, 'print/GraphQLSettings.graphql'),
                    types     : [
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
                null,
            ],
            'Schema-PrinterSettings'                           => [
                new Expected(
                    value     : self::getSchemaContent($data, 'print/PrinterSettings.graphql'),
                    types     : [
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
                    ],
                    directives: [
                        '@schemaDirective',
                        '@codeDirective',
                        '@deprecated',
                        '@scalar',
                        '@mock',
                    ],
                ),
                new PrinterSettings(),
                0,
                0,
                $schemaFactory,
                $printableFactory,
                null,
            ],
            'Schema-PrinterSettings-NoDirectivesDefinitions'   => [
                new Expected(
                    value     : self::getSchemaContent($data, 'print/PrinterSettings-NoDirectivesDefinitions.graphql'),
                    types     : [
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
                    ],
                    directives: [
                        '@schemaDirective',
                        '@codeDirective',
                        '@deprecated',
                        '@scalar',
                        '@mock',
                    ],
                ),
                (new PrinterSettings())
                    ->setPrintDirectiveDefinitions(false),
                0,
                0,
                $schemaFactory,
                $printableFactory,
                null,
            ],
            'Schema-PrinterSettings-NoNormalization'           => [
                new Expected(
                    value     : self::getSchemaContent($data, 'print/PrinterSettings-NoNormalization.graphql'),
                    types     : [
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
                    ],
                    directives: [
                        '@schemaDirective',
                        '@codeDirective',
                        '@deprecated',
                        '@scalar',
                        '@mock',
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
                null,
            ],
            'Schema-PrinterSettings-DirectiveDefinitionFilter' => [
                new Expected(
                    value     : self::getSchemaContent(
                        $data,
                        'print/PrinterSettings-DirectiveDefinitionFilter.graphql',
                    ),
                    types     : [
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
                    ],
                    directives: [
                        '@schemaDirective',
                        '@codeDirective',
                        '@deprecated',
                        '@scalar',
                        '@mock',
                    ],
                ),
                (new PrinterSettings())
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
                null,
            ],
            'Schema-PrinterSettings-TypeDefinitionFilter'      => [
                new Expected(
                    value     : self::getSchemaContent($data, 'print/PrinterSettings-TypeDefinitionFilter.graphql'),
                    types     : [
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
                    ],
                    directives: [
                        '@schemaDirective',
                        '@codeDirective',
                        '@deprecated',
                        '@scalar',
                        '@mock',
                    ],
                ),
                (new PrinterSettings())
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
                null,
            ],
            'Schema-PrinterSettings-Everything'                => [
                new Expected(
                    value     : self::getSchemaContent($data, 'print/PrinterSettings-Everything.graphql'),
                    types     : [
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
                    ],
                    directives: [
                        '@schemaDirective',
                        '@codeDirective',
                        '@deprecated',
                        '@scalar',
                        '@mock',
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
                null,
            ],
        ];
    }

    /**
     * @return array<string, array{
     *      Expected,
     *      ?Settings,
     *      int,
     *      int,
     *      Closure(static): (Schema|string),
     *      Closure(static, Schema): (Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema),
     *      Closure(static, Schema): ((TypeNode&Node)|Type|null)|null,
     *      }>
     */
    public static function dataProviderExportType(): array {
        $data          = TestData::get();
        $schemaFactory = self::getSchemaFactory($data);

        return [
            'CodeUnion'  => [
                new Expected(
                    value     : self::getSchemaContent($data, 'export/CodeUnion.graphql'),
                    types     : [
                        'String',
                        'Boolean',
                        'CodeType',
                        'CodeUnion',
                    ],
                    directives: [
                        '@schemaDirective',
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
                null,
            ],
            'CodeInput'  => [
                new Expected(
                    value     : self::getSchemaContent($data, 'export/CodeInput.graphql'),
                    types     : [
                        'String',
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
                static function (TestCase $test, Schema $schema): Type {
                    $type = $schema->getType('CodeInput');

                    self::assertNotNull($type);

                    return $type;
                },
                null,
            ],
            'SchemaType' => [
                new Expected(
                    value: self::getSchemaContent($data, 'export/SchemaType.graphql'),
                    types: [
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
                    ],
                ),
                (new PrinterSettings())
                    ->setPrintDirectives(false),
                0,
                0,
                $schemaFactory,
                static function (TestCase $test, Schema $schema): Type {
                    $type = $schema->getType('SchemaType');

                    self::assertNotNull($type);

                    return $type;
                },
                null,
            ],
        ];
    }

    /**
     * @return array<string, array{
     *      Expected,
     *      ?Settings,
     *      int,
     *      int,
     *      Closure(static): (Schema|string),
     *      Closure(static, Schema): (Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema),
     *      Closure(static, Schema): ((TypeNode&Node)|Type|null)|null,
     *      }>
     */
    public static function dataProviderPrintType(): array {
        $data          = TestData::get();
        $schemaFactory = self::getSchemaFactory($data);

        return [
            'UnionType'       => [
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
                null,
            ],
            'InputObjectType' => [
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
                            'a' => [
                                'type' => Type::boolean(),
                            ],
                        ],
                    ]);
                },
                null,
            ],
            'InterfaceType'   => [
                new Expected(
                    value: <<<'GRAPHQL'
                        """
                        Description
                        """
                        interface CodeInterface {
                            a: Boolean!
                        }
                        GRAPHQL,
                    types: [
                        'Boolean',
                        'CodeInterface',
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
                null,
            ],
        ];
    }

    /**
     * @return array<string, array{
     *      Expected,
     *      ?Settings,
     *      int,
     *      int,
     *      Closure(static): (Schema|string),
     *      Closure(static, Schema): (Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema),
     *      Closure(static, Schema): ((TypeNode&Node)|Type|null)|null,
     *      }>
     */
    public static function dataProviderPrintNode(): array {
        $data          = TestData::get();
        $schemaFactory = self::getSchemaFactory($data);

        return [
            'UnionTypeDefinitionNode'   => [
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
                null,
            ],
            'InputObjectTypeDefinition' => [
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
                null,
            ],
        ];
    }

    /**
     * @return array<string, array{
     *      Expected,
     *      ?Settings,
     *      int,
     *      int,
     *      Closure(static): (Schema|string),
     *      Closure(static, Schema): (Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema),
     *      Closure(static, Schema): ((TypeNode&Node)|Type|null)|null,
     *      }>
     */
    public static function dataProviderExportNode(): array {
        $data          = TestData::get();
        $schemaFactory = self::getSchemaFactory($data);

        return [
            'UnionTypeDefinitionNode'   => [
                new Expected(
                    value     : self::getSchemaContent($data, 'export/UnionTypeDefinitionNode.graphql'),
                    types     : [
                        'String',
                        'CodeType',
                        'SchemaUnion',
                        'Boolean',
                    ],
                    directives: [
                        '@schemaDirective',
                    ],
                ),
                new PrinterSettings(),
                1,
                0,
                $schemaFactory,
                static function (): Node {
                    return Parser::unionTypeDefinition(
                        'union SchemaUnion = CodeType',
                    );
                },
                null,
            ],
            'InputObjectTypeDefinition' => [
                new Expected(
                    value     : self::getSchemaContent($data, 'export/InputObjectTypeDefinition.graphql'),
                    types     : [
                        'String',
                        'CodeInput',
                        'SchemaInput',
                        'Boolean',
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
                        '"Description" input SchemaInput { a: CodeInput }',
                    );
                },
                null,
            ],
        ];
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    /**
     * @return Closure(static): string
     */
    private static function getSchemaFactory(TestData $data): Closure {
        return static function (TestCase $test) use ($data): string {
            // Types
            $directives = $test->app()->make(DirectiveLocator::class);
            $registry   = $test->app()->make(TypeRegistry::class);
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
            return $data->content('schema.graphql');
        };
    }

    /**
     * @param non-empty-string $path
     */
    private static function getSchemaContent(TestData $data, string $path): string {
        $expected      = $data->content($path);
        static $legacy = InstalledVersions::satisfies(new VersionParser(), 'nuwave/lighthouse', '<6.59.0');

        if ($legacy) {
            // @see https://github.com/nuwave/lighthouse/commit/52962f5366b7774315d7024162798edee109f93b
            $expected = str_replace(
                'Reference to a class that extends `GraphQL\Type\Definition\ScalarType`.',
                'Reference to a class that extends `\\GraphQL\\Type\\Definition\\ScalarType`.',
                $expected,
            );
        }

        return $expected;
    }
    // </editor-fold>
}
