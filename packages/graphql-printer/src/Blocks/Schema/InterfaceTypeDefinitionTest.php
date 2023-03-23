<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Schema;

use GraphQL\Language\Parser;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Schema\InterfaceTypeDefinition
 */
class InterfaceTypeDefinitionTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderToString
     */
    public function testToString(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        InterfaceType $definition,
    ): void {
        $actual = (string) (new InterfaceTypeDefinition($settings, $level, $used, $definition));

        Parser::interfaceTypeDefinition($actual);

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $settings   = new TestSettings();
        $definition = new InterfaceType([
            'name'       => 'A',
            'fields'     => [
                'b' => [
                    'name'    => 'b',
                    'type'    => new ObjectType([
                        'name'   => 'B',
                        'fields' => [
                            'field' => [
                                'type' => Type::string(),
                            ],
                        ],
                    ]),
                    'args'    => [
                        'c' => [
                            'type'    => new ObjectType([
                                'name'   => 'C',
                                'fields' => [
                                    'field' => [
                                        'type' => Type::string(),
                                    ],
                                ],
                            ]),
                            'astNode' => Parser::inputValueDefinition('c: C @c'),
                        ],
                    ],
                    'astNode' => Parser::fieldDefinition('b: B @b'),
                ],
            ],
            'interfaces' => [
                new InterfaceType([
                    'name'   => 'D',
                    'fields' => [
                        'field' => [
                            'type' => Type::string(),
                        ],
                    ],
                ]),
            ],
            'astNode'    => Parser::interfaceTypeDefinition('interface A @a'),
        ]);
        $block      = new InterfaceTypeDefinition($settings, 0, 0, $definition);

        self::assertNotEmpty((string) $block);
        self::assertEquals(['B' => 'B', 'C' => 'C', 'D' => 'D'], $block->getUsedTypes());
        self::assertEquals(['@a' => '@a', '@b' => '@b', '@c' => '@c'], $block->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, InterfaceType}>
     */
    public static function dataProviderToString(): array {
        $settings = (new TestSettings())
            ->setNormalizeFields(false)
            ->setNormalizeInterfaces(false)
            ->setAlwaysMultilineArguments(false)
            ->setAlwaysMultilineInterfaces(false);

        return [
            'description + directives'                    => [
                <<<'STRING'
                """
                Description
                """
                interface Test
                @a
                @b
                @c
                STRING,
                $settings
                    ->setPrintDirectives(true),
                0,
                0,
                new InterfaceType([
                    'name'              => 'Test',
                    'fields'            => [],
                    'astNode'           => Parser::interfaceTypeDefinition('interface Test @a'),
                    'description'       => 'Description',
                    'extensionASTNodes' => [
                        Parser::interfaceTypeExtension('extend interface Test @b'),
                        Parser::interfaceTypeExtension('extend interface Test @c'),
                    ],
                ]),
            ],
            'description + directives + fields'           => [
                <<<'STRING'
                """
                Description
                """
                interface Test
                @a
                {
                    c: C

                    """
                    Description
                    """
                    b(b: Int): B

                    a(a: Int): A
                }
                STRING,
                $settings->setPrintDirectives(true),
                0,
                0,
                new InterfaceType([
                    'name'        => 'Test',
                    'astNode'     => Parser::interfaceTypeDefinition('interface Test @a'),
                    'description' => 'Description',
                    'fields'      => [
                        [
                            'name' => 'c',
                            'type' => new ObjectType([
                                'name'   => 'C',
                                'fields' => [
                                    'field' => [
                                        'type' => Type::string(),
                                    ],
                                ],
                            ]),
                        ],
                        [
                            'name'        => 'b',
                            'type'        => new ObjectType([
                                'name'   => 'B',
                                'fields' => [
                                    'field' => [
                                        'type' => Type::string(),
                                    ],
                                ],
                            ]),
                            'args'        => [
                                'b' => [
                                    'type'   => Type::int(),
                                    'fields' => [
                                        'field' => [
                                            'type' => Type::string(),
                                        ],
                                    ],
                                ],
                            ],
                            'description' => 'Description',
                        ],
                        [
                            'name' => 'a',
                            'type' => new ObjectType([
                                'name'   => 'A',
                                'fields' => [
                                    'field' => [
                                        'type' => Type::string(),
                                    ],
                                ],
                            ]),
                            'args' => [
                                'a' => [
                                    'type' => Type::int(),
                                ],
                            ],
                        ],
                    ],
                ]),
            ],
            'fields'                                      => [
                <<<'STRING'
                interface Test {
                    a: String
                }
                STRING,
                $settings,
                0,
                0,
                new InterfaceType([
                    'name'   => 'Test',
                    'fields' => [
                        'a' => [
                            'type' => Type::string(),
                        ],
                    ],
                ]),
            ],
            'implements + directives + fields'            => [
                <<<'STRING'
                interface Test implements B & A
                @a
                {
                    a: String
                }
                STRING,
                $settings->setPrintDirectives(true),
                0,
                0,
                new InterfaceType([
                    'name'       => 'Test',
                    'astNode'    => Parser::interfaceTypeDefinition('interface Test @a'),
                    'fields'     => [
                        'a' => [
                            'type' => Type::string(),
                        ],
                    ],
                    'interfaces' => [
                        new InterfaceType([
                            'name'   => 'B',
                            'fields' => [
                                'field' => [
                                    'type' => Type::string(),
                                ],
                            ],
                        ]),
                        new InterfaceType([
                            'name'   => 'A',
                            'fields' => [
                                'field' => [
                                    'type' => Type::string(),
                                ],
                            ],
                        ]),
                    ],
                ]),
            ],
            'implements(multiline) + directives + fields' => [
                <<<'STRING'
                interface Test
                implements
                    & B
                    & A
                @a
                {
                    a: String
                }
                STRING,
                $settings->setPrintDirectives(true),
                0,
                120,
                new InterfaceType([
                    'name'       => 'Test',
                    'astNode'    => Parser::interfaceTypeDefinition('interface Test @a'),
                    'fields'     => [
                        'a' => [
                            'type' => Type::string(),
                        ],
                    ],
                    'interfaces' => [
                        new InterfaceType([
                            'name'   => 'B',
                            'fields' => [
                                'field' => [
                                    'type' => Type::string(),
                                ],
                            ],
                        ]),
                        new InterfaceType([
                            'name'   => 'A',
                            'fields' => [
                                'field' => [
                                    'type' => Type::string(),
                                ],
                            ],
                        ]),
                    ],
                ]),
            ],
            'implements(multiline) + fields'              => [
                <<<'STRING'
                interface Test
                implements
                    & B
                    & A
                {
                    a: String
                }
                STRING,
                $settings,
                0,
                120,
                new InterfaceType([
                    'name'       => 'Test',
                    'fields'     => [
                        'a' => [
                            'name' => 'a',
                            'type' => Type::string(),
                        ],
                    ],
                    'interfaces' => [
                        new InterfaceType([
                            'name'   => 'B',
                            'fields' => [
                                'field' => [
                                    'type' => Type::string(),
                                ],
                            ],
                        ]),
                        new InterfaceType([
                            'name'   => 'A',
                            'fields' => [
                                'field' => [
                                    'type' => Type::string(),
                                ],
                            ],
                        ]),
                    ],
                ]),
            ],
            'implements + fields'                         => [
                <<<'STRING'
                interface Test implements B & A {
                    a: String
                }
                STRING,
                $settings,
                0,
                0,
                new InterfaceType([
                    'name'       => 'Test',
                    'fields'     => [
                        'a' => [
                            'type' => Type::string(),
                        ],
                    ],
                    'interfaces' => [
                        new InterfaceType([
                            'name'   => 'B',
                            'fields' => [
                                'field' => [
                                    'type' => Type::string(),
                                ],
                            ],
                        ]),
                        new InterfaceType([
                            'name'   => 'A',
                            'fields' => [
                                'field' => [
                                    'type' => Type::string(),
                                ],
                            ],
                        ]),
                    ],
                ]),
            ],
            'implements(normalized) + fields'             => [
                <<<'STRING'
                interface Test
                implements
                    & A
                    & B
                {
                    a: String
                }
                STRING,
                $settings->setNormalizeInterfaces(true),
                0,
                120,
                new InterfaceType([
                    'name'       => 'Test',
                    'fields'     => [
                        'a' => [
                            'type' => Type::string(),
                        ],
                    ],
                    'interfaces' => [
                        new InterfaceType([
                            'name'   => 'B',
                            'fields' => [
                                'field' => [
                                    'type' => Type::string(),
                                ],
                            ],
                        ]),
                        new InterfaceType([
                            'name'   => 'A',
                            'fields' => [
                                'field' => [
                                    'type' => Type::string(),
                                ],
                            ],
                        ]),
                    ],
                ]),
            ],
            'indent'                                      => [
                <<<'STRING'
                interface Test
                    implements
                        & A
                        & B
                    {
                        a: String
                    }
                STRING,
                $settings->setNormalizeInterfaces(true),
                1,
                120,
                new InterfaceType([
                    'name'       => 'Test',
                    'fields'     => [
                        'a' => [
                            'type' => Type::string(),
                        ],
                    ],
                    'interfaces' => [
                        new InterfaceType([
                            'name'   => 'B',
                            'fields' => [
                                'field' => [
                                    'type' => Type::string(),
                                ],
                            ],
                        ]),
                        new InterfaceType([
                            'name'   => 'A',
                            'fields' => [
                                'field' => [
                                    'type' => Type::string(),
                                ],
                            ],
                        ]),
                    ],
                ]),
            ],
            'implements always multiline'                 => [
                <<<'STRING'
                interface Test
                implements
                    & A
                {
                    a: String
                }
                STRING,
                $settings
                    ->setAlwaysMultilineInterfaces(true),
                0,
                0,
                new InterfaceType([
                    'name'       => 'Test',
                    'fields'     => [
                        'a' => [
                            'type' => Type::string(),
                        ],
                    ],
                    'interfaces' => [
                        new InterfaceType([
                            'name'   => 'A',
                            'fields' => [
                                'field' => [
                                    'type' => Type::string(),
                                ],
                            ],
                        ]),
                    ],
                ]),
            ],
            'args always multiline'                       => [
                <<<'STRING'
                interface Test {
                    """
                    Description
                    """
                    b(
                        b: Int
                    ): B

                    a(
                        a: Int
                    ): A
                }
                STRING,
                $settings->setAlwaysMultilineArguments(true),
                0,
                0,
                new InterfaceType([
                    'name'   => 'Test',
                    'fields' => [
                        'b' => [
                            'type'        => new ObjectType([
                                'name'   => 'B',
                                'fields' => [
                                    'field' => [
                                        'type' => Type::string(),
                                    ],
                                ],
                            ]),
                            'args'        => [
                                'b' => [
                                    'type' => Type::int(),
                                ],
                            ],
                            'description' => 'Description',
                        ],
                        'a' => [
                            'type' => new ObjectType([
                                'name'   => 'A',
                                'fields' => [
                                    'field' => [
                                        'type' => Type::string(),
                                    ],
                                ],
                            ]),
                            'args' => [
                                'a' => [
                                    'type' => Type::int(),
                                ],
                            ],
                        ],
                    ],
                ]),
            ],
        ];
    }
    // </editor-fold>
}
