<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(InterfaceTypeDefinition::class)]
class InterfaceTypeDefinitionTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderSerialize
     */
    public function testSerialize(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        InterfaceTypeDefinitionNode|InterfaceType $definition,
    ): void {
        $collector = new Collector();
        $context   = new Context($settings, null, null);
        $actual    = (new InterfaceTypeDefinition($context, $definition))->serialize($collector, $level, $used);

        if ($expected) {
            Parser::interfaceTypeDefinition($actual);
        }

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $context    = new Context(new TestSettings(), null, null);
        $collector  = new Collector();
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
        $block      = new InterfaceTypeDefinition($context, $definition);
        $content    = $block->serialize($collector, 0, 0);

        self::assertNotEmpty($content);
        self::assertEquals(['A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D'], $collector->getUsedTypes());
        self::assertEquals(['@a' => '@a', '@b' => '@b', '@c' => '@c'], $collector->getUsedDirectives());

        $astCollector = new Collector();
        $astBlock     = new InterfaceTypeDefinition($context, Parser::interfaceTypeDefinition($content));

        self::assertEquals($content, $astBlock->serialize($astCollector, 0, 0));
        self::assertEquals($collector->getUsedTypes(), $astCollector->getUsedTypes());
        self::assertEquals($collector->getUsedDirectives(), $astCollector->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, InterfaceTypeDefinitionNode|InterfaceType}>
     */
    public static function dataProviderSerialize(): array {
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
            'filter'                                      => [
                '',
                $settings
                    ->setTypeDefinitionFilter(static fn () => false),
                0,
                0,
                new InterfaceType([
                    'name'   => 'Test',
                    'fields' => [],
                ]),
            ],
            'ast'                                         => [
                <<<'STRING'
                """
                Description
                """
                interface Test implements B & A
                @a
                {
                    a: String
                }
                STRING,
                $settings
                    ->setPrintDirectives(true)
                    ->setDirectiveFilter(static function (string $directive): bool {
                        return $directive !== 'b';
                    }),
                0,
                0,
                Parser::interfaceTypeDefinition(
                    '"Description" interface Test implements B & A @a @b { a: String }',
                ),
            ],
            'ast + filter'                                => [
                '',
                $settings
                    ->setTypeDefinitionFilter(static fn () => false),
                0,
                0,
                Parser::interfaceTypeDefinition(
                    'interface Test { a: String }',
                ),
            ],
        ];
    }
    // </editor-fold>
}
