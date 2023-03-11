<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Ast;

use GraphQL\Language\AST\BooleanValueNode;
use GraphQL\Language\AST\EnumValueNode;
use GraphQL\Language\AST\FloatValueNode;
use GraphQL\Language\AST\IntValueNode;
use GraphQL\Language\AST\ListValueNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NullValueNode;
use GraphQL\Language\AST\ObjectValueNode;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Language\AST\ValueNode;
use GraphQL\Language\AST\VariableNode;
use GraphQL\Language\Parser;
use GraphQL\Language\Printer;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Ast\ValueNodeBlock
 */
class ValueNodeTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderToString
     *
     * @param ValueNode&Node $node
     */
    public function testToString(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        ValueNode $node,
    ): void {
        $actual = (string) (new ValueNodeBlock($settings, $level, $used, $node));
        $parsed = Parser::valueLiteral($actual);

        self::assertEquals($expected, $actual);

        if (!$settings->isNormalizeArguments()) {
            self::assertEquals(
                Printer::doPrint($node),
                Printer::doPrint($parsed),
            );
        }
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, ValueNode&Node}>
     */
    public static function dataProviderToString(): array {
        $settings = (new TestSettings())
            ->setNormalizeArguments(false);

        return [
            NullValueNode::class                                  => [
                'null',
                $settings,
                0,
                0,
                Parser::valueLiteral('null'),
            ],
            IntValueNode::class                                   => [
                '123',
                $settings,
                0,
                0,
                Parser::valueLiteral('123'),
            ],
            FloatValueNode::class                                 => [
                '123.45',
                $settings,
                0,
                0,
                Parser::valueLiteral('123.45'),
            ],
            BooleanValueNode::class                               => [
                'true',
                $settings,
                0,
                0,
                Parser::valueLiteral('true'),
            ],
            StringValueNode::class                                => [
                '"true"',
                $settings,
                0,
                0,
                Parser::valueLiteral('"true"'),
            ],
            EnumValueNode::class                                  => [
                'Value',
                $settings,
                0,
                0,
                Parser::valueLiteral('Value'),
            ],
            VariableNode::class                                   => [
                '$variable',
                $settings,
                0,
                0,
                Parser::valueLiteral('$variable'),
            ],
            ListValueNode::class.' (short)'                       => [
                '["a", "b", "c"]',
                $settings,
                0,
                0,
                Parser::valueLiteral('["a", "b", "c"]'),
            ],
            ListValueNode::class.' (with block string)'           => [
                <<<'STRING'
                [
                    "string"
                    """
                    Block
                        string
                    """
                ]
                STRING,
                $settings,
                0,
                0,
                Parser::valueLiteral(
                    <<<'STRING'
                    [
                        "string"
                        """
                        Block
                            string
                        """
                    ]
                    STRING,
                ),
            ],
            ListValueNode::class.' (with block string and level)' => [
                <<<'STRING'
                [
                        "string"
                        """
                        Block
                            string
                        """
                    ]
                STRING,
                $settings,
                1,
                0,
                Parser::valueLiteral(
                    <<<'STRING'
                    [
                        "string"
                        """
                        Block
                            string
                        """
                    ]
                    STRING,
                ),
            ],
            ListValueNode::class.' (empty)'                       => [
                <<<'STRING'
                []
                STRING,
                $settings,
                1,
                0,
                Parser::valueLiteral('[]'),
            ],
            ObjectValueNode::class                                => [
                <<<'STRING'
                {
                    object: {
                        a: "a"
                        b: "b"
                    }
                }
                STRING,
                $settings,
                0,
                0,
                Parser::valueLiteral(
                    <<<'STRING'
                {
                    object: {
                        a: "a"
                        b: "b"
                    }
                }
                STRING,
                ),
            ],
            ObjectValueNode::class.' (empty)'                     => [
                <<<'STRING'
                {}
                STRING,
                $settings,
                0,
                0,
                Parser::valueLiteral('{}'),
            ],
            ObjectValueNode::class.' (normalized)'                => [
                <<<'STRING'
                {
                    a: "a"
                    b: "b"
                }
                STRING,
                $settings
                    ->setNormalizeArguments(true),
                0,
                0,
                Parser::valueLiteral(
                    <<<'STRING'
                    {
                        b: "b"
                        a: "a"
                    }
                    STRING,
                ),
            ],
            'all'                                                 => [
                <<<'STRING'
                {
                    int: 123
                    bool: true
                    string: "string"
                    blockString: """
                        Block
                            string
                        """
                    array: [1, 2, 3]
                    object: {
                        a: "a"
                        b: {
                            b: null
                            array: [3]
                            nested: {
                                a: 123
                            }
                        }
                    }
                }
                STRING,
                $settings,
                0,
                0,
                Parser::valueLiteral(
                    <<<'STRING'
                {
                    int: 123
                    bool: true
                    string: "string"
                    blockString: """
                        Block
                            string
                        """
                    array: [
                        1
                        2
                        3
                    ]
                    object: {
                        a: "a"
                        b: {
                            b: null
                            array: [
                                3
                            ]
                            nested: {
                                a: 123
                            }
                        }
                    }
                }
                STRING,
                ),
            ],
        ];
    }
    // </editor-fold>
}
