<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter;

use Exception;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Directive as GraphQLDirective;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings\DefaultSettings;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings\GraphQLSettings;
use LastDragon_ru\LaraASP\GraphQL\Testing\GraphQLExpectedSchema;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\SchemaPrinter\TestSettings;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Nuwave\Lighthouse\Support\Contracts\Directive as LighthouseDirective;

use function str_starts_with;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Printer
 */
class PrinterTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::print
     *
     * @dataProvider dataProviderPrint
     */
    public function testPrint(GraphQLExpectedSchema $expected, ?Settings $settings, int $level): void {
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
                [
                    'name' => 'a',
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
                [
                    'name' => 'a',
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

        // Test
        $printer = $this->app->make(Printer::class)->setSettings($settings)->setLevel($level);
        $schema  = $this->getGraphQLSchema($this->getTestData()->file('~schema.graphql'));
        $actual  = $printer->print($schema);

        $this->assertGraphQLSchemaEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array<mixed>>
     */
    public function dataProviderPrint(): array {
        return [
            'null'                                             => [
                (new GraphQLExpectedSchema(
                    $this->getTestData()->file('~default-settings.graphql'),
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
                    ])
                    ->setUnusedTypes([
                        'ID',
                        'Int',
                        'Float',
                        'CodeInterface',
                        'SchemaEnumUnused',
                        'SchemaInputUnused',
                        'SchemaInterfaceA',
                        'SchemaInterfaceUnused',
                        'SchemaScalarUnused',
                        'SchemaTypeUnused',
                        'SchemaUnionUnused',
                    ])
                    ->setUsedDirectives([
                        '@deprecated',
                    ])
                    ->setUnusedDirectives([
                        '@include',
                        '@skip',
                        '@scalar',
                        '@mock',
                        '@schemaDirective',
                        '@schemaDirectiveUnused',
                        '@codeDirective',
                    ]),
                null,
                0,
            ],
            DefaultSettings::class                             => [
                (new GraphQLExpectedSchema(
                    $this->getTestData()->file('~default-settings.graphql'),
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
                    ])
                    ->setUnusedTypes([
                        'ID',
                        'Int',
                        'Float',
                        'CodeInterface',
                        'SchemaEnumUnused',
                        'SchemaInputUnused',
                        'SchemaInterfaceA',
                        'SchemaInterfaceUnused',
                        'SchemaScalarUnused',
                        'SchemaTypeUnused',
                        'SchemaUnionUnused',
                    ])
                    ->setUsedDirectives([
                        '@deprecated',
                    ])
                    ->setUnusedDirectives([
                        '@include',
                        '@skip',
                        '@scalar',
                        '@mock',
                        '@schemaDirective',
                        '@schemaDirectiveUnused',
                        '@codeDirective',
                    ]),
                new DefaultSettings(),
                0,
            ],
            GraphQLSettings::class                             => [
                (new GraphQLExpectedSchema(
                    $this->getTestData()->file('~graphql-settings.graphql'),
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
                    ->setUnusedTypes([
                        'ID',
                        'Int',
                        'Float',
                    ])
                    ->setUsedDirectives([
                        '@deprecated',
                    ])
                    ->setUnusedDirectives([
                        '@include',
                        '@skip',
                        '@schemaDirective',
                        '@schemaDirectiveUnused',
                        '@codeDirective',
                        '@mock',
                        '@scalar',
                    ]),
                new GraphQLSettings(),
                0,
            ],
            TestSettings::class                                => [
                (new GraphQLExpectedSchema(
                    $this->getTestData()->file('~test-settings.graphql'),
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
                    ])
                    ->setUnusedTypes([
                        'ID',
                        'Float',
                        'CodeInterface',
                        'SchemaEnumUnused',
                        'SchemaInputUnused',
                        'SchemaInterfaceA',
                        'SchemaInterfaceUnused',
                        'SchemaScalarUnused',
                        'SchemaTypeUnused',
                        'SchemaUnionUnused',
                    ])
                    ->setUsedDirectives([
                        '@schemaDirective',
                        '@codeDirective',
                        '@deprecated',
                        '@scalar',
                        '@mock',
                    ])
                    ->setUnusedDirectives([
                        '@include',
                        '@skip',
                        '@schemaDirectiveUnused',
                    ]),
                new TestSettings(),
                0,
            ],
            TestSettings::class.' (no directives definitions)' => [
                (new GraphQLExpectedSchema(
                    $this->getTestData()->file('~test-settings-no-directives-definitions.graphql'),
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
                    ])
                    ->setUnusedTypes([
                        'ID',
                        'Int',
                        'Float',
                        'CodeInterface',
                        'SchemaEnumUnused',
                        'SchemaInputUnused',
                        'SchemaInterfaceA',
                        'SchemaInterfaceUnused',
                        'SchemaScalarUnused',
                        'SchemaTypeUnused',
                        'SchemaUnionUnused',
                    ])
                    ->setUsedDirectives([
                        '@schemaDirective',
                        '@codeDirective',
                        '@deprecated',
                        '@scalar',
                        '@mock',
                    ])
                    ->setUnusedDirectives([
                        '@include',
                        '@skip',
                        '@schemaDirectiveUnused',
                    ]),
                (new TestSettings())
                    ->setPrintDirectiveDefinitions(false),
                0,
            ],
            TestSettings::class.' (directives in description)' => [
                (new GraphQLExpectedSchema(
                    $this->getTestData()->file('~test-settings-directives-in-description.graphql'),
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
                    ])
                    ->setUnusedTypes([
                        'ID',
                        'Int',
                        'Float',
                        'CodeInterface',
                        'SchemaEnumUnused',
                        'SchemaInputUnused',
                        'SchemaInterfaceA',
                        'SchemaInterfaceUnused',
                        'SchemaScalarUnused',
                        'SchemaTypeUnused',
                        'SchemaUnionUnused',
                    ])
                    ->setUsedDirectives([
                        // empty
                    ])
                    ->setUnusedDirectives([
                        '@include',
                        '@skip',
                        '@deprecated',
                        '@schemaDirective',
                        '@schemaDirectiveUnused',
                    ]),
                (new TestSettings())
                    ->setPrintDirectives(false)
                    ->setPrintDirectiveDefinitions(false)
                    ->setPrintDirectivesInDescription(true),
                0,
            ],
            TestSettings::class.' (no normalization)'          => [
                (new GraphQLExpectedSchema(
                    $this->getTestData()->file('~test-settings-no-normalization.graphql'),
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
                    ])
                    ->setUnusedTypes([
                        'ID',
                        'Float',
                        'CodeInterface',
                        'SchemaEnumUnused',
                        'SchemaInputUnused',
                        'SchemaInterfaceA',
                        'SchemaInterfaceUnused',
                        'SchemaScalarUnused',
                        'SchemaTypeUnused',
                        'SchemaUnionUnused',
                    ])
                    ->setUsedDirectives([
                        '@schemaDirective',
                        '@codeDirective',
                        '@deprecated',
                        '@scalar',
                        '@mock',
                    ])
                    ->setUnusedDirectives([
                        '@include',
                        '@skip',
                        '@schemaDirectiveUnused',
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
                    $this->getTestData()->file('~test-settings-directive-definition-filter.graphql'),
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
                    ])
                    ->setUnusedTypes([
                        'ID',
                        'Int',
                        'Float',
                        'CodeInterface',
                        'CodeDirectiveEnum',
                        'CodeDirectiveInput',
                        'CodeDirectiveScalar',
                        'CodeDirectiveScalarCustomClass',
                        'SchemaEnumUnused',
                        'SchemaInputUnused',
                        'SchemaInterfaceA',
                        'SchemaInterfaceUnused',
                        'SchemaScalarUnused',
                        'SchemaTypeUnused',
                        'SchemaUnionUnused',
                    ])
                    ->setUsedDirectives([
                        '@schemaDirective',
                        '@codeDirective',
                        '@deprecated',
                        '@scalar',
                        '@mock',
                    ])
                    ->setUnusedDirectives([
                        '@include',
                        '@skip',
                        '@schemaDirectiveUnused',
                    ]),
                (new TestSettings())
                    ->setDirectiveDefinitionFilter(
                        static function (GraphQLDirective|LighthouseDirective $directive, bool $isStandard): bool {
                            return $isStandard === false
                                && $directive instanceof GraphQLDirective
                                && $directive->name !== 'codeDirective';
                        },
                    ),
                0,
            ],
            TestSettings::class.' (TypeDefinitionFilter)'      => [
                (new GraphQLExpectedSchema(
                    $this->getTestData()->file('~test-settings-type-definition-filter.graphql'),
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
                        'SchemaInterfaceB',
                        'SchemaScalar',
                        'SchemaType',
                        'SchemaUnion',
                        'String',
                    ])
                    ->setUnusedTypes([
                        'ID',
                        'Int',
                        'Float',
                        'CodeInterface',
                        'SchemaEnumUnused',
                        'SchemaInputUnused',
                        'SchemaInterfaceA',
                        'SchemaInterfaceUnused',
                        'SchemaScalarUnused',
                        'SchemaTypeUnused',
                        'SchemaUnionUnused',
                    ])
                    ->setUsedDirectives([
                        '@schemaDirective',
                        '@codeDirective',
                        '@deprecated',
                        '@scalar',
                        '@mock',
                    ])
                    ->setUnusedDirectives([
                        '@include',
                        '@skip',
                        '@schemaDirectiveUnused',
                    ]),
                (new TestSettings())
                    ->setTypeDefinitionFilter(
                        static function (Type $type, bool $isStandard): bool {
                            return $isStandard === false
                                && !str_starts_with($type->name, 'Code');
                        },
                    ),
                0,
            ],
            TestSettings::class.' (everything)'                => [
                (new GraphQLExpectedSchema(
                    $this->getTestData()->file('~test-settings-everything.graphql'),
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
                    ])
                    ->setUnusedTypes([
                        'ID',
                        'Float',
                        'CodeInterface',
                        'SchemaEnumUnused',
                        'SchemaInputUnused',
                        'SchemaInterfaceA',
                        'SchemaInterfaceUnused',
                        'SchemaScalarUnused',
                        'SchemaTypeUnused',
                        'SchemaUnionUnused',
                    ])
                    ->setUsedDirectives([
                        '@schemaDirective',
                        '@codeDirective',
                        '@deprecated',
                        '@scalar',
                        '@mock',
                    ])
                    ->setUnusedDirectives([
                        '@include',
                        '@skip',
                        '@schemaDirectiveUnused',
                    ]),
                (new TestSettings())
                    ->setTypeDefinitionFilter(static fn (): bool => true)
                    ->setDirectiveFilter(static fn (): bool => true)
                    ->setDirectiveDefinitionFilter(static fn (): bool => true),
                0,
            ],
        ];
    }
    // </editor-fold>
}
