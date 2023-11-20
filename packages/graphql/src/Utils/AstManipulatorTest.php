<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Utils;

use Closure;
use Exception;
use GraphQL\Language\AST\ArgumentNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\ListTypeNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Argument;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Type\Definition\EnumValueDefinition;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputType;
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
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

use function array_keys;
use function array_map;
use function assert;
use function is_string;

// @phpcs:disable Generic.Files.LineLength.TooLong

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
            <<<'GRAPHQL'
            interface InterfaceA {
                id: ID!
            }

            interface InterfaceB implements InterfaceA & InterfaceC {
                id: ID!
            }

            type ObjectA implements InterfaceA & InterfaceB {
                id: ID!
            }
            GRAPHQL,
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
            <<<'GRAPHQL'
            extend scalar Int @aDirective @bDirective
            scalar CustomScalar @bDirective @cDirective
            extend scalar CustomScalar @aDirective

            type Query {
                test(arg: String @aDirective @cDirective): Test @all @bDirective
            }

            type Test {
                id: ID!
            }
            GRAPHQL,
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
     *
     * @param Closure(AstManipulator): (ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode|ObjectType|InterfaceType)                                      $definitionFactory
     * @param Closure(AstManipulator, ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode|ObjectType|InterfaceType): (FieldDefinitionNode|FieldDefinition) $fieldFactory
     */
    public function testAddArgument(
        Exception|string $expected,
        string $schema,
        Closure $definitionFactory,
        Closure $fieldFactory,
        string $name,
        string $type,
        mixed $default,
        ?string $description,
    ): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $manipulator = $this->getManipulator($schema);
        $definition  = $definitionFactory($manipulator);
        $field       = $fieldFactory($manipulator, $definition);

        $manipulator->addArgument($definition, $field, $name, $type, $default, $description);

        if (is_string($expected)) {
            $this->useGraphQLSchema($manipulator->getDocument());
            $this->assertGraphQLExportableEquals($expected, $definition);
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
            static function (mixed $argument) use ($manipulator): bool {
                return $manipulator->getDirective($argument, AstManipulatorTest_ADirective::class) !== null;
            },
        );

        self::assertInstanceOf(InputValueDefinitionNode::class, $nodeArgument);
        self::assertEquals('b', $nodeArgument->name->value);

        $fieldArgument = $manipulator->findArgument(
            $field,
            static function (mixed $argument) use ($manipulator): bool {
                return $manipulator->getDirective($argument, AstManipulatorTest_ADirective::class) !== null;
            },
        );

        self::assertInstanceOf(Argument::class, $fieldArgument);
        self::assertEquals('b', $fieldArgument->name);

        $directiveArgument = $manipulator->findArgument(
            Parser::directive('@aDirective(a: "a", b: "b")'),
            static function (mixed $argument) use ($manipulator): bool {
                return $manipulator->getName($argument) === 'b';
            },
        );

        self::assertInstanceOf(ArgumentNode::class, $directiveArgument);
        self::assertEquals('b', $directiveArgument->name->value);
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

    /**
     * @dataProvider dataProviderIsDeprecated
     */
    public function testIsDeprecated(
        bool $expected,
        Node|Argument|EnumValueDefinition|FieldDefinition|InputObjectField $node,
    ): void {
        self::assertEquals($expected, $this->getManipulator()->isDeprecated($node));
    }

    /**
     * @dataProvider dataProviderSetFieldType
     *
     * @param Closure(AstManipulator): (ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode|ObjectType|InterfaceType)                                      $definitionFactory
     * @param Closure(AstManipulator, ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode|ObjectType|InterfaceType): (FieldDefinitionNode|FieldDefinition) $fieldFactory
     * @param NamedTypeNode|ListTypeNode|NonNullTypeNode|(Type&InputType)                                                                                   $type
     */
    public function testSetFieldType(
        Exception|string $expected,
        string $schema,
        Closure $definitionFactory,
        Closure $fieldFactory,
        TypeNode|Type $type,
    ): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $manipulator = $this->getManipulator($schema);
        $definition  = $definitionFactory($manipulator);
        $field       = $fieldFactory($manipulator, $definition);

        $manipulator->setFieldType($definition, $field, $type);

        if (is_string($expected)) {
            $this->useGraphQLSchema($manipulator->getDocument());
            $this->assertGraphQLExportableEquals($expected, $definition);
        }
    }

    /**
     * @dataProvider dataProviderSetArgumentType
     *
     * @param Closure(AstManipulator): (ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode|ObjectType|InterfaceType)                                                                         $definitionFactory
     * @param Closure(AstManipulator, ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode|ObjectType|InterfaceType): (FieldDefinitionNode|FieldDefinition)                                    $fieldFactory
     * @param Closure(AstManipulator, ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode|ObjectType|InterfaceType, FieldDefinitionNode|FieldDefinition): (InputValueDefinitionNode|Argument) $argumentFactory
     * @param NamedTypeNode|ListTypeNode|NonNullTypeNode|(Type&InputType)                                                                                                                      $type
     */
    public function testSetArgumentType(
        Exception|string $expected,
        string $schema,
        Closure $definitionFactory,
        Closure $fieldFactory,
        Closure $argumentFactory,
        TypeNode|Type $type,
    ): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $manipulator = $this->getManipulator($schema);
        $definition  = $definitionFactory($manipulator);
        $field       = $fieldFactory($manipulator, $definition);
        $arg         = $argumentFactory($manipulator, $definition, $field);

        $manipulator->setArgumentType($definition, $field, $arg, $type);

        if (is_string($expected)) {
            $this->useGraphQLSchema($manipulator->getDocument());
            $this->assertGraphQLExportableEquals($expected, $definition);
        }
    }

    public function testFindField(): void {
        // Prepare
        $manipulator = $this->getManipulator();
        $node        = Parser::objectTypeDefinition(
            <<<'GRAPHQL'
            type Test {
                a: Int
                b: String
                c: Boolean
            }
            GRAPHQL,
        );
        $type        = new ObjectType([
            'name'    => 'Test',
            'fields'  => [
                new FieldDefinition([
                    'name' => 'a',
                    'type' => Type::int(),
                ]),
                new FieldDefinition([
                    'name' => 'b',
                    'type' => Type::string(),
                ]),
                new FieldDefinition([
                    'name' => 'c',
                    'type' => Type::boolean(),
                ]),
            ],
            'astNode' => $node,
        ]);

        // Test
        $nodeField = $manipulator->findField(
            $node,
            static function (mixed $field) use ($manipulator): bool {
                return $manipulator->getTypeName($field) === Type::INT;
            },
        );

        self::assertInstanceOf(FieldDefinitionNode::class, $nodeField);
        self::assertEquals('a', $nodeField->name->value);

        $typeField = $manipulator->findField(
            $type,
            static function (mixed $field) use ($manipulator): bool {
                return $manipulator->getTypeName($field) === Type::BOOLEAN;
            },
        );

        self::assertInstanceOf(FieldDefinition::class, $typeField);
        self::assertEquals('c', $typeField->name);
    }

    //</editor-fold>

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

    /**
     * @return Closure(AstManipulator): (ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode|ObjectType|InterfaceType)
     */
    protected static function getDefinitionFactory(string $name): Closure {
        return static function (
            AstManipulator $manipulator,
        ) use (
            $name,
        ): ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode {
            $definition = $manipulator->getTypeDefinition($name);

            assert(
                $definition instanceof ObjectTypeDefinitionNode
                || $definition instanceof InterfaceTypeDefinitionNode,
            );

            return $definition;
        };
    }

    /**
     * @return Closure(AstManipulator, ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode|ObjectType|InterfaceType): (FieldDefinitionNode|FieldDefinition)
     */
    protected static function getFieldFactory(string $name): Closure {
        return static function (
            AstManipulator $manipulator,
            ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode|ObjectType|InterfaceType $definition,
        ) use (
            $name,
        ): FieldDefinitionNode|FieldDefinition {
            $field = $manipulator->getField($definition, $name);

            self::assertNotNull($field);

            return $field;
        };
    }

    /**
     * @return Closure(AstManipulator, ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode|ObjectType|InterfaceType, FieldDefinitionNode|FieldDefinition): (InputValueDefinitionNode|Argument)
     */
    protected static function getArgumentFactory(string $name): Closure {
        return static function (
            AstManipulator $manipulator,
            ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode|ObjectType|InterfaceType $definition,
            FieldDefinitionNode|FieldDefinition $field,
        ) use (
            $name,
        ): InputValueDefinitionNode {
            $argument = $manipulator->getArgument($field, $name);

            self::assertInstanceOf(InputValueDefinitionNode::class, $argument);

            return $argument;
        };
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{
     *      Exception|string,
     *      string,
     *      Closure(AstManipulator): (ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode|ObjectType|InterfaceType),
     *      Closure(AstManipulator, ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode|ObjectType|InterfaceType): (FieldDefinitionNode|FieldDefinition),
     *      string,
     *      string,
     *      mixed,
     *      ?string,
     *      }>
     */
    public static function dataProviderAddArgument(): array {
        $schema = <<<'GRAPHQL'
            type Query implements InterfaceB & InterfaceC {
                a(a: Int): Int @mock
                b: Boolean @mock
            }

            interface InterfaceA {
                a(a: Int): Int
            }

            interface InterfaceB implements InterfaceA {
                a(a: Int): Int
                b: Boolean
            }

            interface InterfaceC {
                a(a: Int): Int
            }
        GRAPHQL;

        return [
            'argument exists'                          => [
                new ArgumentAlreadyDefined('type Query { a(a) }'),
                $schema,
                self::getDefinitionFactory('Query'),
                self::getFieldFactory('a'),
                'a',
                'Boolean',
                null,
                null,
            ],
            'argument without description and default' => [
                <<<'GRAPHQL'
                type Query
                implements
                    & InterfaceB
                    & InterfaceC
                {
                    a(
                        a: Int
                    ): Int
                    @mock

                    b(
                        argument: Boolean
                    ): Boolean
                    @mock
                }

                interface InterfaceA {
                    a(
                        a: Int
                    ): Int
                }

                interface InterfaceB
                implements
                    & InterfaceA
                {
                    a(
                        a: Int
                    ): Int

                    b(
                        argument: Boolean
                    ): Boolean
                }

                interface InterfaceC {
                    a(
                        a: Int
                    ): Int
                }

                GRAPHQL,
                $schema,
                self::getDefinitionFactory('Query'),
                self::getFieldFactory('b'),
                'argument',
                'Boolean',
                null,
                null,
            ],
            'argument without description'             => [
                <<<'GRAPHQL'
                type Query
                implements
                    & InterfaceB
                    & InterfaceC
                {
                    a(
                        a: Int
                    ): Int
                    @mock

                    b(
                        argument: String = "String value"
                    ): Boolean
                    @mock
                }

                interface InterfaceA {
                    a(
                        a: Int
                    ): Int
                }

                interface InterfaceB
                implements
                    & InterfaceA
                {
                    a(
                        a: Int
                    ): Int

                    b(
                        argument: String = "String value"
                    ): Boolean
                }

                interface InterfaceC {
                    a(
                        a: Int
                    ): Int
                }

                GRAPHQL,
                $schema,
                self::getDefinitionFactory('Query'),
                self::getFieldFactory('b'),
                'argument',
                'String',
                'String value',
                null,
            ],
            'argument'                                 => [
                <<<'GRAPHQL'
                type Query
                implements
                    & InterfaceB
                    & InterfaceC
                {
                    a(
                        a: Int
                    ): Int
                    @mock

                    b(
                        """
                        Description \"""
                        "multiline"
                        with \\
                        """
                        argument: Int = 123
                    ): Boolean
                    @mock
                }

                interface InterfaceA {
                    a(
                        a: Int
                    ): Int
                }

                interface InterfaceB
                implements
                    & InterfaceA
                {
                    a(
                        a: Int
                    ): Int

                    b(
                        """
                        Description \"""
                        "multiline"
                        with \\
                        """
                        argument: Int = 123
                    ): Boolean
                }

                interface InterfaceC {
                    a(
                        a: Int
                    ): Int
                }

                GRAPHQL,
                $schema,
                self::getDefinitionFactory('Query'),
                self::getFieldFactory('b'),
                'argument',
                'Int',
                123,
                <<<'DESCRIPTION'
                Description """
                "multiline"
                with \\
                DESCRIPTION,
            ],
            'FieldDefinition'                          => [
                <<<'GRAPHQL'
                type Query {
                    a(
                        argument: [String!] = ["a", "b", "c"]
                    ): Int
                }

                GRAPHQL,
                $schema,
                static function (): ObjectType {
                    return new ObjectType([
                        'name'   => 'Query',
                        'fields' => [
                            'a' => [
                                'type' => Type::int(),
                            ],
                        ],
                    ]);
                },
                self::getFieldFactory('a'),
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
                <<<'GRAPHQL'
                field: String
                @astManipulatorTest_A
                GRAPHQL,
                Parser::fieldDefinition('field: String'),
                AstManipulatorTest_ADirective::class,
                [],
            ],
            'field: with arguments'             => [
                <<<'GRAPHQL'
                field: String
                @astManipulatorTest_A(
                    a: 123
                    b: "b"
                )
                GRAPHQL,
                Parser::fieldDefinition('field: String'),
                AstManipulatorTest_ADirective::class,
                [
                    'a' => 123,
                    'b' => 'b',
                ],
            ],
            'input argument: without arguments' => [
                <<<'GRAPHQL'
                argument: String = 123
                @astManipulatorTest_A
                GRAPHQL,
                Parser::inputValueDefinition('argument: String = 123'),
                AstManipulatorTest_ADirective::class,
                [],
            ],
            'input argument: with arguments'    => [
                <<<'GRAPHQL'
                argument: String
                @astManipulatorTest_A(
                    a: 123
                    b: "b"
                )
                GRAPHQL,
                Parser::inputValueDefinition('argument: String'),
                AstManipulatorTest_ADirective::class,
                [
                    'a' => 123,
                    'b' => 'b',
                ],
            ],
            'astNode'                           => [
                <<<'GRAPHQL'
                argument: String
                @astManipulatorTest_A
                GRAPHQL,
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

    /**
     * @return array<string, array{
     *      bool,
     *      Node|Argument|EnumValueDefinition|FieldDefinition|InputObjectField,
     *      }>
     */
    public static function dataProviderIsDeprecated(): array {
        return [
            'argument: deprecated' => [
                true,
                new Argument([
                    'name'              => 'argument',
                    'type'              => Type::string(),
                    'deprecationReason' => '',
                ]),
            ],
            'argument'             => [
                false,
                new Argument([
                    'name' => 'argument',
                    'type' => Type::string(),
                ]),
            ],
            'node: deprecated'     => [
                true,
                Parser::field(
                    'argument: String @deprecated',
                ),
            ],
            'node'                 => [
                false,
                Parser::field(
                    'argument: String',
                ),
            ],
        ];
    }

    /**
     * @return array<string, array{
     *      Exception|string,
     *      string,
     *      Closure(AstManipulator): (ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode|ObjectType|InterfaceType),
     *      Closure(AstManipulator, ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode|ObjectType|InterfaceType): (FieldDefinitionNode|FieldDefinition),
     *      NamedTypeNode|ListTypeNode|NonNullTypeNode|(Type&InputType),
     *      }>
     */
    public static function dataProviderSetFieldType(): array {
        $schema = <<<'GRAPHQL'
            type Query implements InterfaceB & InterfaceC {
                a: Int @mock
            }

            interface InterfaceA {
                a: Int
            }

            interface InterfaceB implements InterfaceA {
                a: Int
            }

            interface InterfaceC {
                a: Int
            }
        GRAPHQL;

        return [
            'type'      => [
                <<<'GRAPHQL'
                type Query
                implements
                    & InterfaceB
                    & InterfaceC
                {
                    a: Boolean
                    @mock
                }

                interface InterfaceA {
                    a: Boolean
                }

                interface InterfaceB
                implements
                    & InterfaceA
                {
                    a: Boolean
                }

                interface InterfaceC {
                    a: Boolean
                }

                GRAPHQL,
                $schema,
                self::getDefinitionFactory('Query'),
                self::getFieldFactory('a'),
                Type::boolean(),
            ],
            'interface' => [
                <<<'GRAPHQL'
                interface InterfaceC {
                    a: Boolean
                }

                GRAPHQL,
                $schema,
                self::getDefinitionFactory('InterfaceC'),
                self::getFieldFactory('a'),
                Type::boolean(),
            ],
        ];
    }

    /**
     * @return array<string, array{
     *      Exception|string,
     *      string,
     *      Closure(AstManipulator): (ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode|ObjectType|InterfaceType),
     *      Closure(AstManipulator, ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode|ObjectType|InterfaceType): (FieldDefinitionNode|FieldDefinition),
     *      Closure(AstManipulator, ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode|ObjectType|InterfaceType, FieldDefinitionNode|FieldDefinition): (InputValueDefinitionNode|Argument),
     *      NamedTypeNode|ListTypeNode|NonNullTypeNode|(Type&InputType),
     *      }>
     */
    public static function dataProviderSetArgumentType(): array {
        $schema = <<<'GRAPHQL'
            type Query implements InterfaceB & InterfaceC {
                a(a: Int, b: String): Int @mock
            }

            interface InterfaceA {
                a(a: Int, b: String): Int
            }

            interface InterfaceB implements InterfaceA {
                a(a: Int, b: String): Int
            }

            interface InterfaceC {
                a(a: Int, b: String): Int
            }
        GRAPHQL;

        return [
            'type'      => [
                <<<'GRAPHQL'
                type Query
                implements
                    & InterfaceB
                    & InterfaceC
                {
                    a(
                        a: Int
                        b: Int
                    ): Int
                    @mock
                }

                interface InterfaceA {
                    a(
                        a: Int
                        b: Int
                    ): Int
                }

                interface InterfaceB
                implements
                    & InterfaceA
                {
                    a(
                        a: Int
                        b: Int
                    ): Int
                }

                interface InterfaceC {
                    a(
                        a: Int
                        b: Int
                    ): Int
                }

                GRAPHQL,
                $schema,
                self::getDefinitionFactory('Query'),
                self::getFieldFactory('a'),
                self::getArgumentFactory('b'),
                Type::int(),
            ],
            'interface' => [
                <<<'GRAPHQL'
                interface InterfaceC {
                    a(
                        a: Boolean
                        b: String
                    ): Int
                }

                GRAPHQL,
                $schema,
                self::getDefinitionFactory('InterfaceC'),
                self::getFieldFactory('a'),
                self::getArgumentFactory('a'),
                Type::boolean(),
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
    #[Override]
    public static function definition(): string {
        return 'directive @astManipulatorTest_A(a: Int, b: String) on OBJECT | SCALAR';
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class AstManipulatorTest_BDirective implements Directive {
    #[Override]
    public static function definition(): string {
        return 'directive @astManipulatorTest_B on OBJECT | SCALAR';
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class AstManipulatorTest_CDirective implements Directive {
    #[Override]
    public static function definition(): string {
        return 'directive @astManipulatorTest_C on OBJECT | SCALAR';
    }
}
