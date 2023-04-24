<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Utils;

use Exception;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Argument;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\ArgumentAlreadyDefined;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\NotImplemented;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Nuwave\Lighthouse\Schema\AST\ASTBuilder;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\AllDirective;
use Nuwave\Lighthouse\Schema\SchemaBuilder;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Nuwave\Lighthouse\Support\Contracts\Directive;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

use function array_keys;
use function array_map;
use function is_string;

/**
 * @internal
 */
#[CoversClass(AstManipulator::class)]
class AstManipulatorTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    public function testGetInterfaces(): void {
        // Object
        $types       = $this->app->make(TypeRegistry::class);
        $manipulator = $this->getManipulator(
            <<<'GraphQL'
            interface InterfaceA {
                id: ID!
            }

            interface InterfaceB implements InterfaceA & InterfaceC {
                id: ID!
            }

            type ObjectA implements InterfaceA & InterfaceB {
                id: ID!
            }
            GraphQL,
        );
        $interface   = new InterfaceType([
            'name'       => 'InterfaceC',
            'interfaces' => [
                static function (): InterfaceType {
                    return new InterfaceType([
                        'name'   => 'InterfaceD',
                        'fields' => [
                            'id' => [
                                'type' => Type::nonNull(Type::id()),
                            ],
                        ],
                    ]);
                },
            ],
            'fields'     => [
                'id' => [
                    'type' => Type::nonNull(Type::id()),
                ],
            ],
        ]);

        $types->register($interface);
        $types->register(
            new ObjectType([
                'name'       => 'ObjectB',
                'interfaces' => [$interface],
                'fields'     => [
                    'id' => [
                        'type' => Type::nonNull(Type::id()),
                    ],
                ],
            ]),
        );

        // Object
        $object = $manipulator->getTypeDefinition('ObjectA');

        self::assertInstanceOf(ObjectTypeDefinitionNode::class, $object);
        self::assertEquals(
            [
                'InterfaceA',
                'InterfaceB',
                'InterfaceC',
                'InterfaceD',
            ],
            array_keys(
                $manipulator->getInterfaces($object),
            ),
        );

        // ObjectType
        $objectType = $manipulator->getTypeDefinition('ObjectB');

        self::assertInstanceOf(ObjectType::class, $objectType);
        self::assertEquals(
            [
                'InterfaceC',
                'InterfaceD',
            ],
            array_keys(
                $manipulator->getInterfaces($objectType),
            ),
        );

        // Interface
        $interface = $manipulator->getTypeDefinition('InterfaceB');

        self::assertInstanceOf(InterfaceTypeDefinitionNode::class, $interface);
        self::assertEquals(
            [
                'InterfaceA',
                'InterfaceC',
                'InterfaceD',
            ],
            array_keys(
                $manipulator->getInterfaces($interface),
            ),
        );

        // InterfaceType
        $interfaceType = $manipulator->getTypeDefinition('InterfaceC');

        self::assertInstanceOf(InterfaceType::class, $interfaceType);
        self::assertEquals(
            [
                'InterfaceD',
            ],
            array_keys(
                $manipulator->getInterfaces($interfaceType),
            ),
        );
    }

    public function testGetDirectives(): void {
        // Types
        $types = $this->app->make(TypeRegistry::class);

        $types->register(
            new CustomScalarType([
                'name' => 'CustomScalar',
            ]),
        );

        // Directives
        $locator = $this->app->make(DirectiveLocator::class);

        $locator->setResolved('aDirective', AstManipulatorTest_ADirective::class);
        $locator->setResolved('bDirective', AstManipulatorTest_BDirective::class);
        $locator->setResolved('cDirective', AstManipulatorTest_CDirective::class);

        // Schema
        $this->useGraphQLSchema(
            <<<'GraphQL'
            extend scalar Int @aDirective @bDirective
            scalar CustomScalar @bDirective @cDirective
            extend scalar CustomScalar @aDirective

            type Query {
                test(arg: String @aDirective @cDirective): Test @all @bDirective
            }

            type Test {
                id: ID!
            }
            GraphQL,
        );

        // Prepare
        $map         = static function (Directive $directive): string {
            return $directive::class;
        };
        $manipulator = $this->getManipulator();

        // Another class
        self::assertEquals(
            [
                // empty
            ],
            array_map(
                $map,
                $manipulator->getDirectives(
                    $manipulator->getTypeDefinition('CustomScalar'),
                    stdClass::class,
                ),
            ),
        );

        // Scalar node
        self::assertEquals(
            [
                AstManipulatorTest_BDirective::class,
                AstManipulatorTest_CDirective::class,
            ],
            array_map(
                $map,
                $manipulator->getDirectives(
                    $manipulator->getTypeDefinition('CustomScalar'),
                    Directive::class,
                ),
            ),
        );

        // Type
        self::assertEquals(
            [
                // Not supported by Lighthouse yet :(
            ],
            array_map(
                $map,
                $manipulator->getDirectives(
                    Type::int(),
                    Directive::class,
                ),
            ),
        );

        // Field
        $schema   = $this->app->make(SchemaBuilder::class)->schema();
        $query    = $schema->getQueryType();
        $field    = $manipulator->getField($query, 'test');
        $expected = [
            AllDirective::class,
            AstManipulatorTest_BDirective::class,
        ];

        self::assertInstanceOf(FieldDefinition::class, $field);
        self::assertNotNull($field->astNode);
        self::assertEquals(
            $expected,
            array_map(
                $map,
                $manipulator->getDirectives(
                    $field,
                    Directive::class,
                ),
            ),
        );
        self::assertEquals(
            $expected,
            array_map(
                $map,
                $manipulator->getDirectives(
                    $field->astNode,
                    Directive::class,
                ),
            ),
        );

        // Argument
        $argument = $manipulator->getArgument($field, 'arg');
        $expected = [
            AstManipulatorTest_ADirective::class,
            AstManipulatorTest_CDirective::class,
        ];

        self::assertInstanceOf(Argument::class, $argument);
        self::assertNotNull($argument->astNode);
        self::assertEquals(
            $expected,
            array_map(
                $map,
                $manipulator->getDirectives(
                    $argument,
                    Directive::class,
                ),
            ),
        );
        self::assertEquals(
            $expected,
            array_map(
                $map,
                $manipulator->getDirectives(
                    $argument->astNode,
                    Directive::class,
                ),
            ),
        );
    }

    /**
     * @dataProvider dataProviderAddArgument
     */
    public function testAddArgument(
        Exception|string $expected,
        FieldDefinitionNode|FieldDefinition $node,
        string $name,
        string $type,
        mixed $default,
        ?string $description,
    ): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $manipulator = $this->getManipulator();

        $manipulator->addArgument($node, $name, $type, $default, $description);

        if (is_string($expected)) {
            $this->assertGraphQLPrintableEquals($expected, $node);
        }
    }

    public function testFindArgument(): void {
        // Directives
        $locator = $this->app->make(DirectiveLocator::class);

        $locator->setResolved('aDirective', AstManipulatorTest_ADirective::class);

        // Prepare
        $manipulator = $this->getManipulator();
        $node        = Parser::fieldDefinition('field(a: String, b: Int @aDirective): String');
        $field       = new FieldDefinition([
            'name'    => 'field',
            'type'    => Type::string(),
            'args'    => [
                'a' => [
                    'type'    => Type::string(),
                    'astNode' => Parser::inputValueDefinition('a: String'),
                ],
                'b' => [
                    'type'    => Type::int(),
                    'astNode' => Parser::inputValueDefinition('b: Int @aDirective'),
                ],
            ],
            'astNode' => $node,
        ]);

        // Test
        $nodeArgument = $manipulator->findArgument(
            $node,
            static function (InputValueDefinitionNode|Argument $argument) use ($manipulator): bool {
                return $manipulator->getDirective($argument, AstManipulatorTest_ADirective::class) !== null;
            },
        );

        self::assertInstanceOf(InputValueDefinitionNode::class, $nodeArgument);
        self::assertEquals('b', $nodeArgument->name->value);

        $fieldArgument = $manipulator->findArgument(
            $field,
            static function (InputValueDefinitionNode|Argument $argument) use ($manipulator): bool {
                return $manipulator->getDirective($argument, AstManipulatorTest_ADirective::class) !== null;
            },
        );

        self::assertInstanceOf(Argument::class, $fieldArgument);
        self::assertEquals('b', $fieldArgument->name);
    }

    /**
     * @dataProvider dataProviderAddDirective
     *
     * @param class-string<Directive> $directive
     * @param array<string, mixed>    $arguments
     */
    public function testAddDirective(
        Exception|string $expected,
        FieldDefinitionNode|InputValueDefinitionNode|Argument $node,
        string $directive,
        array $arguments,
    ): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $locator = $this->app->make(DirectiveLocator::class);

        $locator->setResolved(
            DirectiveLocator::directiveName($directive),
            $directive,
        );

        $manipulator = $this->getManipulator();

        $manipulator->addDirective($node, $directive, $arguments);

        if (is_string($expected)) {
            $this->assertGraphQLPrintableEquals($expected, $node);
        }
    }

    // <editor-fold desc="Helpers">
    // =========================================================================
    protected function getManipulator(string $schema = null): AstManipulator {
        $document    = $schema
            ? DocumentAST::fromSource($schema)
            : $this->app->make(ASTBuilder::class)->documentAST();
        $manipulator = $this->app->make(AstManipulator::class, [
            'document' => $document,
        ]);

        return $manipulator;
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{
     *      Exception|string,
     *      FieldDefinitionNode|FieldDefinition,
     *      string,
     *      string,
     *      mixed,
     *      ?string,
     *      }>
     */
    public static function dataProviderAddArgument(): array {
        return [
            'argument exists'                          => [
                new ArgumentAlreadyDefined('argument'),
                Parser::fieldDefinition('field(argument: String): String'),
                'argument',
                'Boolean',
                null,
                null,
            ],
            'argument without description and default' => [
                <<<'GraphQL'
                field(
                    a: String
                    b: Boolean
                ): String
                GraphQL,
                Parser::fieldDefinition('field(a: String): String'),
                'b',
                'Boolean',
                null,
                null,
            ],
            'argument without description'             => [
                <<<'GraphQL'
                field(
                    argument: String = "String value"
                ): String
                GraphQL,
                Parser::fieldDefinition('field: String'),
                'argument',
                'String',
                'String value',
                null,
            ],
            'argument'                                 => [
                <<<'GraphQL'
                field(
                    """
                    Description \"""
                    "multiline"
                    with \\ \n
                    """
                    argument: Int = 123
                ): String
                GraphQL,
                Parser::fieldDefinition('field: String'),
                'argument',
                'Int',
                123,
                <<<'DESCRIPTION'
                Description """
                "multiline"
                with \\ \n
                DESCRIPTION,
            ],
            'FieldDefinition'                          => [
                <<<'GraphQL'
                field(
                    argument: [String!] = ["a", "b", "c"]
                ): String
                GraphQL,
                new FieldDefinition([
                    'name' => 'field',
                    'type' => static fn () => Type::string(),
                ]),
                'argument',
                '[String!]',
                [
                    'a',
                    'b',
                    'c',
                ],
                null,
            ],
        ];
    }

    /**
     * @return array<string, array{
     *      Exception|string,
     *      FieldDefinitionNode|InputValueDefinitionNode|Argument,
     *      class-string<Directive>,
     *      array<string, mixed>
     *      }>
     */
    public static function dataProviderAddDirective(): array {
        return [
            'field: without arguments'          => [
                <<<'GraphQL'
                field: String
                @astManipulatorTest_A
                GraphQL,
                Parser::fieldDefinition('field: String'),
                AstManipulatorTest_ADirective::class,
                [],
            ],
            'field: with arguments'             => [
                <<<'GraphQL'
                field: String
                @astManipulatorTest_A(
                    a: 123
                    b: "b"
                )
                GraphQL,
                Parser::fieldDefinition('field: String'),
                AstManipulatorTest_ADirective::class,
                [
                    'a' => 123,
                    'b' => 'b',
                ],
            ],
            'input argument: without arguments' => [
                <<<'GraphQL'
                argument: String = 123
                @astManipulatorTest_A
                GraphQL,
                Parser::inputValueDefinition('argument: String = 123'),
                AstManipulatorTest_ADirective::class,
                [],
            ],
            'input argument: with arguments'    => [
                <<<'GraphQL'
                argument: String
                @astManipulatorTest_A(
                    a: 123
                    b: "b"
                )
                GraphQL,
                Parser::inputValueDefinition('argument: String'),
                AstManipulatorTest_ADirective::class,
                [
                    'a' => 123,
                    'b' => 'b',
                ],
            ],
            'astNode'                           => [
                <<<'GraphQL'
                argument: String
                @astManipulatorTest_A
                GraphQL,
                new Argument([
                    'name'    => 'argument',
                    'type'    => static fn () => Type::string(),
                    'astNode' => Parser::inputValueDefinition('argument: String'),
                ]),
                AstManipulatorTest_ADirective::class,
                [],
            ],
            'no astNode'                        => [
                new NotImplemented(Argument::class),
                new Argument([
                    'name' => 'argument',
                    'type' => static fn () => Type::string(),
                ]),
                AstManipulatorTest_ADirective::class,
                [],
            ],
        ];
    }
    // </editor-fold>
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class AstManipulatorTest_ADirective implements Directive {
    public static function definition(): string {
        return 'directive @astManipulatorTest_A(a: Int, b: String) on OBJECT | SCALAR';
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class AstManipulatorTest_BDirective implements Directive {
    public static function definition(): string {
        return 'directive @astManipulatorTest_B on OBJECT | SCALAR';
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class AstManipulatorTest_CDirective implements Directive {
    public static function definition(): string {
        return 'directive @astManipulatorTest_C on OBJECT | SCALAR';
    }
}
