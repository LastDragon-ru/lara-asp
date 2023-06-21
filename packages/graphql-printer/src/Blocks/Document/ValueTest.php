<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\BooleanValueNode;
use GraphQL\Language\AST\EnumValueNode;
use GraphQL\Language\AST\FloatValueNode;
use GraphQL\Language\AST\IntValueNode;
use GraphQL\Language\AST\ListValueNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NullValueNode;
use GraphQL\Language\AST\ObjectValueNode;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Language\AST\ValueNode;
use GraphQL\Language\AST\VariableNode;
use GraphQL\Language\Parser;
use GraphQL\Language\Printer;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Value::class)]
class ValueTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderToString
     *
     * @param ValueNode&Node            $node
     * @param (TypeNode&Node)|Type|null $type
     */
    public function testToString(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        ValueNode $node,
        TypeNode|Type|null $type,
    ): void {
        $context = new Context($settings, null, null);
        $actual  = (string) (new Value($context, $level, $used, $node, $type));
        $parsed  = null;

        if ($expected) {
            $parsed = Parser::valueLiteral($actual);
        }

        self::assertEquals($expected, $actual);

        if ($parsed !== null && !$settings->isNormalizeArguments()) {
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
     * @return array<string,array{string, Settings, int, int, ValueNode&Node, TypeNode|Type|null}>
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
                null,
            ],
            IntValueNode::class                                   => [
                '123',
                $settings,
                0,
                0,
                Parser::valueLiteral('123'),
                null,
            ],
            FloatValueNode::class                                 => [
                '123.45',
                $settings,
                0,
                0,
                Parser::valueLiteral('123.45'),
                null,
            ],
            BooleanValueNode::class                               => [
                'true',
                $settings,
                0,
                0,
                Parser::valueLiteral('true'),
                null,
            ],
            StringValueNode::class                                => [
                '"true"',
                $settings,
                0,
                0,
                Parser::valueLiteral('"true"'),
                null,
            ],
            EnumValueNode::class                                  => [
                'Value',
                $settings,
                0,
                0,
                Parser::valueLiteral('Value'),
                null,
            ],
            VariableNode::class                                   => [
                '$variable',
                $settings,
                0,
                0,
                Parser::valueLiteral('$variable'),
                null,
            ],
            ListValueNode::class.' (short)'                       => [
                '["a", "b", "c"]',
                $settings,
                0,
                0,
                Parser::valueLiteral('["a", "b", "c"]'),
                null,
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
                null,
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
                null,
            ],
            ListValueNode::class.' (empty)'                       => [
                <<<'STRING'
                []
                STRING,
                $settings,
                1,
                0,
                Parser::valueLiteral('[]'),
                null,
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
                null,
            ],
            ObjectValueNode::class.' (empty)'                     => [
                <<<'STRING'
                {}
                STRING,
                $settings,
                0,
                0,
                Parser::valueLiteral('{}'),
                null,
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
                null,
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
                null,
            ],
            'filter'                                              => [
                '',
                $settings
                    ->setTypeFilter(static fn () => false),
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
                Type::int(),
            ],
            'filter: no type'                                     => [
                '"abc"',
                $settings
                    ->setTypeFilter(static fn () => false),
                0,
                0,
                Parser::valueLiteral(
                    '"abc"',
                ),
                null,
            ],
        ];
    }
    // </editor-fold>
}
