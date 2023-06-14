<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\FieldDefinition as GraphQLFieldDefinition;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
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
#[CoversClass(FieldDefinition::class)]
#[CoversClass(ArgumentsDefinition::class)]
class FieldDefinitionTest extends TestCase {
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
        FieldDefinitionNode|GraphQLFieldDefinition $definition,
    ): void {
        $context = new Context($settings, null, null);
        $actual  = (string) (new FieldDefinition($context, $level, $used, $definition));

        Parser::fieldDefinition($actual);

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $context    = new Context(new TestSettings(), null, null);
        $definition = new GraphQLFieldDefinition([
            'name'    => 'A',
            'type'    => new NonNull(
                new ObjectType([
                    'name'   => 'A',
                    'fields' => static function () use (&$definition): array {
                        return [$definition];
                    },
                ]),
            ),
            'astNode' => Parser::fieldDefinition('a: A @a'),
        ]);
        $block      = new FieldDefinition($context, 0, 0, $definition);

        self::assertNotEmpty((string) $block);
        self::assertEquals(['A' => 'A'], $block->getUsedTypes());
        self::assertEquals(['@a' => '@a'], $block->getUsedDirectives());

        $ast = new FieldDefinition($context, 0, 0, Parser::fieldDefinition((string) $block));

        self::assertEquals($block->getUsedTypes(), $ast->getUsedTypes());
        self::assertEquals($block->getUsedDirectives(), $ast->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, FieldDefinitionNode|GraphQLFieldDefinition}>
     */
    public static function dataProviderToString(): array {
        $settings = (new TestSettings())
            ->setNormalizeArguments(false)
            ->setAlwaysMultilineArguments(false);
        $object   = new ObjectType([
            'name'   => 'Test',
            'fields' => [
                'field' => [
                    'type' => Type::int(),
                ],
            ],
        ]);

        return [
            'without args'               => [
                <<<'STRING'
                """
                Description
                """
                test: Test!
                @a
                STRING,
                $settings
                    ->setPrintDirectives(true),
                0,
                0,
                new GraphQLFieldDefinition([
                    'name'        => 'test',
                    'type'        => new NonNull($object),
                    'astNode'     => Parser::fieldDefinition('test: Test! @a'),
                    'description' => 'Description',
                ]),
            ],
            'with args (short)'          => [
                <<<'STRING'
                """
                Description
                """
                test(a: [String!] = ["aaaaaaaaaaaaaaaaaaaaaaaaaa"], b: Int): Test!
                STRING,
                $settings,
                0,
                0,
                new GraphQLFieldDefinition([
                    'name'        => 'test',
                    'type'        => new NonNull($object),
                    'args'        => [
                        'a' => [
                            'type'         => new ListOfType(new NonNull(Type::string())),
                            'defaultValue' => [
                                'aaaaaaaaaaaaaaaaaaaaaaaaaa',
                            ],
                        ],
                        'b' => [
                            'type' => Type::int(),
                        ],
                    ],
                    'description' => 'Description',
                ]),
            ],
            'with args (long)'           => [
                <<<'STRING'
                test(
                    b: Int

                    """
                    Description
                    """
                    a: String! = "aaaaaaaaaaaaaaaaaaaaaaaaaa"
                ): Test!
                STRING,
                $settings,
                0,
                0,
                new GraphQLFieldDefinition([
                    'name' => 'test',
                    'type' => new NonNull($object),
                    'args' => [
                        'b' => [
                            'type' => Type::int(),
                        ],
                        'a' => [
                            'type'         => new NonNull(Type::string()),
                            'description'  => 'Description',
                            'defaultValue' => 'aaaaaaaaaaaaaaaaaaaaaaaaaa',
                        ],
                    ],
                ]),
            ],
            'with args normalized'       => [
                <<<'STRING'
                test(a: String, b: Int): Test!
                STRING,
                $settings->setNormalizeArguments(true),
                0,
                0,
                new GraphQLFieldDefinition([
                    'name' => 'test',
                    'type' => new NonNull($object),
                    'args' => [
                        'b' => [
                            'type' => Type::int(),
                        ],
                        'a' => [
                            'type' => Type::string(),
                        ],
                    ],
                ]),
            ],
            'with args always multiline' => [
                <<<'STRING'
                test(
                    b: Int
                    a: String
                ): Test!
                STRING,
                $settings->setAlwaysMultilineArguments(true),
                0,
                0,
                new GraphQLFieldDefinition([
                    'name' => 'test',
                    'type' => new NonNull($object),
                    'args' => [
                        'b' => [
                            'type' => Type::int(),
                        ],
                        'a' => [
                            'type' => Type::string(),
                        ],
                    ],
                ]),
            ],
            'indent'                     => [
                <<<'STRING'
                test(
                        a: String
                        b: Int
                    ): Test!
                STRING,
                $settings->setNormalizeArguments(true),
                1,
                120,
                new GraphQLFieldDefinition([
                    'name' => 'test',
                    'type' => new NonNull($object),
                    'args' => [
                        'b' => [
                            'type' => Type::int(),
                        ],
                        'a' => [
                            'type' => Type::string(),
                        ],
                    ],
                ]),
            ],
            'deprecationReason (empty)'  => [
                <<<'STRING'
                test: Test!
                @deprecated
                STRING,
                $settings
                    ->setPrintDirectives(true),
                0,
                0,
                new GraphQLFieldDefinition([
                    'name'              => 'test',
                    'type'              => new NonNull($object),
                    'deprecationReason' => '',
                ]),
            ],
            'deprecationReason'          => [
                <<<'STRING'
                test: Test!
                @deprecated(reason: "test")
                STRING,
                $settings
                    ->setPrintDirectives(true),
                0,
                0,
                new GraphQLFieldDefinition([
                    'name'              => 'test',
                    'type'              => new NonNull($object),
                    'deprecationReason' => 'test',
                    'astNode'           => Parser::fieldDefinition(
                        'test: Test! @deprecated(reason: "should be ignored")',
                    ),
                ]),
            ],
            'ast'                        => [
                <<<'STRING'
                test(
                    """
                    Description
                    """
                    a: String! = "aaaaaaaaaaaaaaaaaaaaaaaaaa"
                ): Test!
                @a
                STRING,
                $settings,
                0,
                0,
                Parser::fieldDefinition(
                    'test("Description" a: String! = "aaaaaaaaaaaaaaaaaaaaaaaaaa"): Test! @a',
                ),
            ],
        ];
    }
    // </editor-fold>
}
