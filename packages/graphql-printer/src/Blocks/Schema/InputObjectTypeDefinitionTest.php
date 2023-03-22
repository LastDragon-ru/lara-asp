<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Schema;

use GraphQL\Language\Parser;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Schema\InputObjectTypeDefinition
 * @covers \LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Schema\InputFieldsDefinition
 */
class InputObjectTypeDefinitionTest extends TestCase {
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
        InputObjectType $definition,
    ): void {
        $actual = (string) (new InputObjectTypeDefinition(
            $settings,
            $level,
            $used,
            $definition,
        ));

        Parser::inputObjectTypeDefinition($actual);

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $settings   = new TestSettings();
        $definition = new InputObjectType([
            'name'    => 'A',
            'fields'  => [
                'b' => [
                    'name'    => 'b',
                    'type'    => new InputObjectType([
                        'name'   => 'B',
                        'fields' => [
                            'field' => [
                                'type' => Type::string(),
                            ],
                        ],
                    ]),
                    'astNode' => Parser::inputValueDefinition('b: B @a'),
                ],
            ],
            'astNode' => Parser::inputObjectTypeDefinition('input A @b'),
        ]);
        $block      = new InputObjectTypeDefinition($settings, 0, 0, $definition);

        self::assertNotEmpty((string) $block);
        self::assertEquals(['B' => 'B'], $block->getUsedTypes());
        self::assertEquals(['@a' => '@a', '@b' => '@b'], $block->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, InputObjectType}>
     */
    public static function dataProviderToString(): array {
        $settings = (new TestSettings())
            ->setNormalizeFields(false);

        return [
            'description + directives'          => [
                <<<'STRING'
                """
                Description
                """
                input Test
                @a
                STRING,
                $settings
                    ->setPrintDirectives(true),
                0,
                0,
                new InputObjectType([
                    'name'        => 'Test',
                    'fields'      => [],
                    'astNode'     => Parser::inputObjectTypeDefinition('input Test @a'),
                    'description' => 'Description',
                ]),
            ],
            'description + directives + fields' => [
                <<<'STRING'
                """
                Description
                """
                input Test
                @a
                {
                    c: C

                    """
                    Description
                    """
                    b: B

                    a: A
                }
                STRING,
                $settings->setPrintDirectives(true),
                0,
                0,
                new InputObjectType([
                    'name'        => 'Test',
                    'astNode'     => Parser::inputObjectTypeDefinition('input Test @a'),
                    'description' => 'Description',
                    'fields'      => [
                        'c' => [
                            'type' => new InputObjectType([
                                'name'   => 'C',
                                'fields' => [
                                    'field' => [
                                        'type' => Type::string(),
                                    ],
                                ],
                            ]),
                        ],
                        'b' => [
                            'type'        => new InputObjectType([
                                'name'   => 'B',
                                'fields' => [
                                    'field' => [
                                        'type' => Type::string(),
                                    ],
                                ],
                            ]),
                            'description' => 'Description',
                        ],
                        'a' => [
                            'type' => new InputObjectType([
                                'name'   => 'A',
                                'fields' => [
                                    'field' => [
                                        'type' => Type::string(),
                                    ],
                                ],
                            ]),
                        ],
                    ],
                ]),
            ],
            'fields'                            => [
                <<<'STRING'
                input Test {
                    a: String
                }
                STRING,
                $settings,
                0,
                0,
                new InputObjectType([
                    'name'   => 'Test',
                    'fields' => [
                        'a' => [
                            'type' => Type::string(),
                        ],
                    ],
                ]),
            ],
            'indent'                            => [
                <<<'STRING'
                input Test {
                        a: String
                    }
                STRING,
                $settings->setNormalizeInterfaces(true),
                1,
                120,
                new InputObjectType([
                    'name'   => 'Test',
                    'fields' => [
                        'a' => [
                            'type' => Type::string(),
                        ],
                    ],
                ]),
            ],
        ];
    }
    // </editor-fold>
}
