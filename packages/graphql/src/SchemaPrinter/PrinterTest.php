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
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\SchemaPrinter\TestSettings;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Nuwave\Lighthouse\Support\Contracts\Directive as LighthouseDirective;

use function array_values;
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
     *
     * @param array{schema:string,usedTypes:array<string>,unusedTypes:array<string>,directives:array<string>} $expected
     */
    public function testPrint(array $expected, ?Settings $settings, int $level): void {
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
        $output  = $this->getTestData()->content($expected['schema']);
        $printer = $this->app->make(Printer::class)->setSettings($settings)->setLevel($level);
        $schema  = $this->getGraphQLSchema($this->getTestData()->file('~schema.graphql'));
        $actual  = $printer->print($schema);

        self::assertEquals($output, (string) $actual);
        self::assertEqualsCanonicalizing($expected['usedTypes'], array_values($actual->getUsedTypes()));
        self::assertEqualsCanonicalizing($expected['unusedTypes'], array_values($actual->getUnusedTypes()));
        self::assertEqualsCanonicalizing($expected['directives'], array_values($actual->getUsedDirectives()));
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
                [
                    'schema'      => '~default-settings.graphql',
                    'usedTypes'   => [
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
                    ],
                    'unusedTypes' => [
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
                    ],
                    'directives'  => [
                        '@deprecated',
                    ],
                ],
                null,
                0,
            ],
            DefaultSettings::class                             => [
                [
                    'schema'      => '~default-settings.graphql',
                    'usedTypes'   => [
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
                    ],
                    'unusedTypes' => [
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
                    ],
                    'directives'  => [
                        '@deprecated',
                    ],
                ],
                new DefaultSettings(),
                0,
            ],
            GraphQLSettings::class                             => [
                [
                    'schema'      => '~graphql-settings.graphql',
                    'usedTypes'   => [
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
                    'unusedTypes' => [
                        'ID',
                        'Int',
                        'Float',
                    ],
                    'directives'  => [
                        '@deprecated',
                    ],
                ],
                new GraphQLSettings(),
                0,
            ],
            TestSettings::class                                => [
                [
                    'schema'      => '~test-settings.graphql',
                    'usedTypes'   => [
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
                    ],
                    'unusedTypes' => [
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
                    ],
                    'directives'  => [
                        '@schemaDirective',
                        '@codeDirective',
                        '@deprecated',
                        '@scalar',
                        '@mock',
                    ],
                ],
                new TestSettings(),
                0,
            ],
            TestSettings::class.' (no directives definitions)' => [
                [
                    'schema'      => '~test-settings-no-directives-definitions.graphql',
                    'usedTypes'   => [
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
                    ],
                    'unusedTypes' => [
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
                    ],
                    'directives'  => [
                        '@schemaDirective',
                        '@codeDirective',
                        '@deprecated',
                        '@scalar',
                        '@mock',
                    ],
                ],
                (new TestSettings())
                    ->setPrintDirectiveDefinitions(false),
                0,
            ],
            TestSettings::class.' (directives in description)' => [
                [
                    'schema'      => '~test-settings-directives-in-description.graphql',
                    'usedTypes'   => [
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
                    ],
                    'unusedTypes' => [
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
                    ],
                    'directives'  => [
                        // empty
                    ],
                ],
                (new TestSettings())
                    ->setPrintDirectives(false)
                    ->setPrintDirectiveDefinitions(false)
                    ->setPrintDirectivesInDescription(true),
                0,
            ],
            TestSettings::class.' (no normalization)'          => [
                [
                    'schema'      => '~test-settings-no-normalization.graphql',
                    'usedTypes'   => [
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
                    ],
                    'unusedTypes' => [
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
                    ],
                    'directives'  => [
                        '@schemaDirective',
                        '@codeDirective',
                        '@deprecated',
                        '@scalar',
                        '@mock',
                    ],
                ],
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
                [
                    'schema'      => '~test-settings-directive-definition-filter.graphql',
                    'usedTypes'   => [
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
                    ],
                    'unusedTypes' => [
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
                    ],
                    'directives'  => [
                        '@schemaDirective',
                        '@codeDirective',
                        '@deprecated',
                        '@scalar',
                        '@mock',
                    ],
                ],
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
                [
                    'schema'      => '~test-settings-type-definition-filter.graphql',
                    'usedTypes'   => [
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
                    ],
                    'unusedTypes' => [
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
                    ],
                    'directives'  => [
                        '@schemaDirective',
                        '@codeDirective',
                        '@deprecated',
                        '@scalar',
                        '@mock',
                    ],
                ],
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
                [
                    'schema'      => '~test-settings-everything.graphql',
                    'usedTypes'   => [
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
                    ],
                    'unusedTypes' => [
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
                    ],
                    'directives'  => [
                        '@schemaDirective',
                        '@codeDirective',
                        '@deprecated',
                        '@scalar',
                        '@mock',
                    ],
                ],
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
