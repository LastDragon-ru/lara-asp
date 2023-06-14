<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(InputObjectTypeDefinition::class)]
#[CoversClass(InputFieldsDefinition::class)]
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
        InputObjectTypeDefinitionNode|InputObjectType $definition,
    ): void {
        $context = new Context($settings, null, null);
        $actual  = (string) (new InputObjectTypeDefinition(
            $context,
            $level,
            $used,
            $definition,
        ));

        if ($expected) {
            Parser::inputObjectTypeDefinition($actual);
        }

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $context    = new Context(new TestSettings(), null, null);
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
        $block      = new InputObjectTypeDefinition($context, 0, 0, $definition);

        self::assertNotEmpty((string) $block);
        self::assertEquals(['B' => 'B'], $block->getUsedTypes());
        self::assertEquals(['@a' => '@a', '@b' => '@b'], $block->getUsedDirectives());

        $ast = new InputObjectTypeDefinition($context, 0, 0, Parser::inputObjectTypeDefinition((string) $block));

        self::assertEquals($block->getUsedTypes(), $ast->getUsedTypes());
        self::assertEquals($block->getUsedDirectives(), $ast->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, InputObjectTypeDefinitionNode|InputObjectType}>
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
                @b
                @c
                STRING,
                $settings
                    ->setPrintDirectives(true),
                0,
                0,
                new InputObjectType([
                    'name'              => 'Test',
                    'fields'            => [],
                    'astNode'           => Parser::inputObjectTypeDefinition('input Test @a'),
                    'description'       => 'Description',
                    'extensionASTNodes' => [
                        Parser::inputObjectTypeExtension('extend input Test @b'),
                        Parser::inputObjectTypeExtension('extend input Test @c'),
                    ],
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
            'filter'                            => [
                '',
                $settings
                    ->setTypeDefinitionFilter(static fn () => false),
                0,
                0,
                new InputObjectType([
                    'name'   => 'Test',
                    'fields' => [],
                ]),
            ],
            'ast'                               => [
                <<<'STRING'
                """
                Description
                """
                input Test
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
                Parser::inputObjectTypeDefinition(
                    '"Description" input Test @a @b { a: String }',
                ),
            ],
            'ast + filter'                      => [
                '',
                $settings
                    ->setTypeDefinitionFilter(static fn () => false),
                0,
                0,
                Parser::inputObjectTypeDefinition(
                    'input Test @a { a: String }',
                ),
            ],
        ];
    }
    // </editor-fold>
}
