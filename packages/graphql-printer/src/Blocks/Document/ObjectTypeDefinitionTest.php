<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\Parser;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(ObjectTypeDefinition::class)]
class ObjectTypeDefinitionTest extends TestCase {
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
        ObjectType $definition,
    ): void {
        $context = new Context($settings, null, null);
        $actual  = (string) (new ObjectTypeDefinition($context, $level, $used, $definition));

        if ($expected) {
            Parser::objectTypeDefinition($actual);
        }

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $context    = new Context(new TestSettings(), null, null);
        $definition = new ObjectType([
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
            'astNode'    => Parser::objectTypeDefinition('type A @a'),
        ]);
        $block      = new ObjectTypeDefinition($context, 0, 0, $definition);

        self::assertNotEmpty((string) $block);
        self::assertEquals(['B' => 'B', 'C' => 'C', 'D' => 'D'], $block->getUsedTypes());
        self::assertEquals(['@a' => '@a', '@b' => '@b', '@c' => '@c'], $block->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, ObjectType}>
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
                type Test
                @a
                @b
                @c
                STRING,
                $settings
                    ->setPrintDirectives(true),
                0,
                0,
                new ObjectType([
                    'name'              => 'Test',
                    'fields'            => [],
                    'astNode'           => Parser::objectTypeDefinition('type Test @a'),
                    'description'       => 'Description',
                    'extensionASTNodes' => [
                        Parser::objectTypeExtension('extend type Test @b'),
                        Parser::objectTypeExtension('extend type Test @c'),
                    ],
                ]),
            ],
            'description + directives + fields'           => [
                <<<'STRING'
                """
                Description
                """
                type Test
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
                new ObjectType([
                    'name'        => 'Test',
                    'astNode'     => Parser::objectTypeDefinition('type Test @a'),
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
                                    'type' => Type::int(),
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
                type Test {
                    a: String
                }
                STRING,
                $settings,
                0,
                0,
                new ObjectType([
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
                type Test implements B & A
                @a
                {
                    a: String
                }
                STRING,
                $settings->setPrintDirectives(true),
                0,
                0,
                new ObjectType([
                    'name'       => 'Test',
                    'astNode'    => Parser::objectTypeDefinition('type Test @a'),
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
                type Test
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
                new ObjectType([
                    'name'       => 'Test',
                    'astNode'    => Parser::objectTypeDefinition('type Test @a'),
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
                type Test
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
                new ObjectType([
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
            'implements + fields'                         => [
                <<<'STRING'
                type Test implements B & A {
                    a: String
                }
                STRING,
                $settings,
                0,
                0,
                new ObjectType([
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
                type Test
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
                new ObjectType([
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
                type Test
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
                new ObjectType([
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
                type Test
                implements
                    & B
                {
                    a: String
                }
                STRING,
                $settings
                    ->setAlwaysMultilineInterfaces(true),
                0,
                0,
                new ObjectType([
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
                    ],
                ]),
            ],
            'args always multiline'                       => [
                <<<'STRING'
                type Test {
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
                new ObjectType([
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
            'filter'                                      => [
                '',
                $settings
                    ->setTypeDefinitionFilter(static fn () => false),
                0,
                0,
                new ObjectType([
                    'name'   => 'Test',
                    'fields' => [],
                ]),
            ],
        ];
    }
    // </editor-fold>
}
