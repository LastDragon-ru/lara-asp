<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter;

use Exception;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQL\Testing\GraphQLExpectedType;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\SchemaPrinter\TestSettings;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\TypeRegistry;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\SchemaTypePrinter
 */
class SchemaTypePrinterTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::print
     *
     * @dataProvider dataProviderPrint
     */
    public function testPrint(GraphQLExpectedType $expected, ?Settings $settings, int $level, Type|string $type): void {
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
        $printer = $this->app->make(SchemaTypePrinter::class)->setSettings($settings)->setLevel($level);
        $schema  = $this->getGraphQLSchema($this->getTestData()->file('~schema.graphql'));
        $actual  = $printer->print($schema, $type);

        $this->assertGraphQLSchemaTypeEquals($expected, $actual, $schema);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array<mixed>>
     */
    public function dataProviderPrint(): array {
        return [
            'CodeUnion'  => [
                (new GraphQLExpectedType(
                    $this->getTestData()->file('~CodeUnion.graphql'),
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
                            'name' => 'CodeType',
                        ]),
                    ],
                ]),
            ],
            'CodeInput'  => [
                (new GraphQLExpectedType(
                    $this->getTestData()->file('~CodeInput.graphql'),
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
                    $this->getTestData()->file('~SchemaType.graphql'),
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
                        'CodeDirectiveEnum',
                        'SchemaEnum',
                        'Int',
                        'CodeDirectiveInput',
                        'CodeDirectiveScalar',
                        'CodeDirectiveScalarCustomClass',
                        'CodeType',
                    ])
                    ->setUsedDirectives([
                        '@codeDirective',
                        '@deprecated',
                        '@schemaDirective',
                        '@scalar',
                    ]),
                (new TestSettings())
                    ->setPrintDirectives(false)
                    ->setPrintDirectivesInDescription(true),
                0,
                'SchemaType',
            ],
        ];
    }
    // </editor-fold>
}
