<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Printer\Blocks;

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
use LastDragon_ru\LaraASP\GraphQL\Printer\Settings;
use LastDragon_ru\LaraASP\GraphQL\Printer\Settings\DefaultSettings;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\Printer\Blocks\Value
 */
class ValueTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::__toString
     *
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
        $actual = (string) (new Value($settings, $level, $used, $node));
        $parsed = Parser::valueLiteral($actual);

        self::assertEquals($expected, $actual);
        self::assertInstanceOf(ValueNode::class, $parsed);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, ValueNode&Node}>
     */
    public function dataProviderToString(): array {
        return [
            NullValueNode::class                                  => [
                'null',
                new DefaultSettings(),
                0,
                0,
                Parser::valueLiteral('null'),
            ],
            IntValueNode::class                                   => [
                '123',
                new DefaultSettings(),
                0,
                0,
                Parser::valueLiteral('123'),
            ],
            FloatValueNode::class                                 => [
                '123.45',
                new DefaultSettings(),
                0,
                0,
                Parser::valueLiteral('123.45'),
            ],
            BooleanValueNode::class                               => [
                'true',
                new DefaultSettings(),
                0,
                0,
                Parser::valueLiteral('true'),
            ],
            StringValueNode::class                                => [
                '"true"',
                new DefaultSettings(),
                0,
                0,
                Parser::valueLiteral('"true"'),
            ],
            EnumValueNode::class                                  => [
                'Value',
                new DefaultSettings(),
                0,
                0,
                Parser::valueLiteral('Value'),
            ],
            VariableNode::class                                   => [
                '$variable',
                new DefaultSettings(),
                0,
                0,
                Parser::valueLiteral('$variable'),
            ],
            ListValueNode::class.' (short)'                       => [
                '["a", "b", "c"]',
                new DefaultSettings(),
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
                new class() extends DefaultSettings {
                    public function getIndent(): string {
                        return '    ';
                    }
                },
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
                new class() extends DefaultSettings {
                    public function getIndent(): string {
                        return '    ';
                    }
                },
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
            ObjectValueNode::class                                => [
                <<<'STRING'
                {
                    object: {
                        a: "a"
                        b: "b"
                    }
                }
                STRING,
                new class() extends DefaultSettings {
                    public function getIndent(): string {
                        return '    ';
                    }
                },
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
                new class() extends DefaultSettings {
                    public function getIndent(): string {
                        return '    ';
                    }
                },
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
