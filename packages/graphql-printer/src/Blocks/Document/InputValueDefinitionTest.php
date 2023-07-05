<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Argument;
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
#[CoversClass(InputValueDefinition::class)]
class InputValueDefinitionTest extends TestCase {
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
        InputValueDefinitionNode|Argument $definition,
    ): void {
        $context = new Context($settings, null, null);
        $actual  = (new InputValueDefinition($context, $level, $used, $definition))->serialize($level, $used);

        Parser::inputValueDefinition($actual);

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $context    = new Context(new TestSettings(), null, null);
        $definition = new Argument([
            'name'    => 'a',
            'type'    => new NonNull(
                new ObjectType([
                    'name'   => 'A',
                    'fields' => [
                        'field' => [
                            'type' => Type::string(),
                        ],
                    ],
                ]),
            ),
            'astNode' => Parser::inputValueDefinition('test: Test! @a'),
        ]);
        $block      = new InputValueDefinition($context, 0, 0, $definition);
        $content    = $block->serialize(0, 0);

        self::assertNotEmpty($content);
        self::assertEquals(['A' => 'A'], $block->getUsedTypes());
        self::assertEquals(['@a' => '@a'], $block->getUsedDirectives());

        $ast = new InputValueDefinition($context, 0, 0, Parser::inputValueDefinition($content));

        self::assertEquals($block->getUsedTypes(), $ast->getUsedTypes());
        self::assertEquals($block->getUsedDirectives(), $ast->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, InputValueDefinitionNode|Argument}>
     */
    public static function dataProviderSerialize(): array {
        $settings = new TestSettings();

        return [
            'without value'             => [
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
                new Argument([
                    'name'        => 'test',
                    'type'        => new NonNull(
                        new ObjectType([
                            'name'   => 'Test',
                            'fields' => [
                                'field' => [
                                    'type' => Type::string(),
                                ],
                            ],
                        ]),
                    ),
                    'astNode'     => Parser::inputValueDefinition('test: Test! @a'),
                    'description' => 'Description',
                ]),
            ],
            'with value (short)'        => [
                <<<'STRING'
                """
                Description
                """
                test: [String!] = ["aaaaaaaaaaaaaaaaaaaaaaaaaa"]
                STRING,
                $settings,
                0,
                0,
                new Argument([
                    'name'         => 'test',
                    'type'         => new ListOfType(new NonNull(Type::string())),
                    'defaultValue' => [
                        'aaaaaaaaaaaaaaaaaaaaaaaaaa',
                    ],
                    'description'  => 'Description',
                ]),
            ],
            'with value (long)'         => [
                <<<'STRING'
                """
                Description
                """
                test: [String!] = [
                    "aaaaaaaaaaaaaaaaaaaaaaaaaa"
                ]
                STRING,
                $settings
                    ->setLineLength(20),
                0,
                120,
                new Argument([
                    'name'         => 'test',
                    'type'         => new ListOfType(new NonNull(Type::string())),
                    'defaultValue' => [
                        'aaaaaaaaaaaaaaaaaaaaaaaaaa',
                    ],
                    'description'  => 'Description',
                ]),
            ],
            'indent'                    => [
                <<<'STRING'
                """
                    Description
                    """
                    test: [String!] = [
                        "aaaaaaaaaaaaaaaaaaaaaaaaaa"
                    ]
                STRING,
                $settings
                    ->setLineLength(40),
                1,
                70,
                new Argument([
                    'name'         => 'test',
                    'type'         => new ListOfType(new NonNull(Type::string())),
                    'defaultValue' => [
                        'aaaaaaaaaaaaaaaaaaaaaaaaaa',
                    ],
                    'description'  => 'Description',
                ]),
            ],
            'deprecationReason (empty)' => [
                <<<'STRING'
                test: String
                @deprecated
                STRING,
                $settings
                    ->setPrintDirectives(true),
                0,
                0,
                new Argument([
                    'name'              => 'test',
                    'type'              => Type::string(),
                    'deprecationReason' => '',
                ]),
            ],
            'deprecationReason'         => [
                <<<'STRING'
                test: String
                @deprecated(
                    reason: "test"
                )
                STRING,
                $settings
                    ->setPrintDirectives(true),
                0,
                0,
                new Argument([
                    'name'              => 'test',
                    'type'              => Type::string(),
                    'deprecationReason' => 'test',
                    'astNode'           => Parser::inputValueDefinition(
                        'test: Test! @deprecated(reason: "should be ignored")',
                    ),
                ]),
            ],
            'ast'                       => [
                <<<'STRING'
                """
                Description
                """
                test: [String!] = [
                    "aaaaaaaaaaaaaaaaaaaaaaaaaa"
                ]
                @directive
                STRING,
                $settings
                    ->setLineLength(20),
                0,
                120,
                Parser::inputValueDefinition(
                    <<<'STRING'
                    "Description"
                    test: [String!] = ["aaaaaaaaaaaaaaaaaaaaaaaaaa"]
                    @directive
                    STRING,
                ),
            ],
        ];
    }
    // </editor-fold>
}
