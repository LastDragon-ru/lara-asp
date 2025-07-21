<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast;

use LastDragon_ru\DiyParser\Ast\Cursor;
use LastDragon_ru\GlobMatcher\Glob\Options;
use LastDragon_ru\GlobMatcher\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(PatternListNode::class)]
final class PatternListNodeTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderToRegex')]
    public function testToRegex(string $expected, PatternListNode $node, Options $options): void {
        self::assertSame($expected, $node::toRegex($options, new Cursor($node)));
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<string, array{string, PatternListNode, Options}>
     */
    public static function dataProviderToRegex(): array {
        $children = [
            new PatternNode([new StringNode('a')]),
            new PatternNode([new StringNode('b')]),
            new PatternNode([new StringNode('c')]),
        ];

        return [
            'ZeroOrOne'  => [
                '(?:(?:a)|(?:b)|(?:c))?',
                new PatternListNode(PatternListQuantifier::ZeroOrOne, $children),
                new Options(),
            ],
            'ZeroOrMore' => [
                '(?:(?:a)|(?:b)|(?:c))*',
                new PatternListNode(PatternListQuantifier::ZeroOrMore, $children),
                new Options(),
            ],
            'OneOrMore'  => [
                '(?:(?:a)|(?:b)|(?:c))+',
                new PatternListNode(PatternListQuantifier::OneOrMore, $children),
                new Options(),
            ],
            'OneOf'      => [
                '(?:(?:a)|(?:b)|(?:c))',
                new PatternListNode(PatternListQuantifier::OneOf, $children),
                new Options(),
            ],
            'Not'        => [
                '(?:(?:a)(?:(?:(?:b)|(?:(?:c)(?:(?:(?!(?:(?:d)|(?:e))(?:(?:f)(?:h)))[^/]*?))(?:f))|(?:g)))(?:h))',
                new PatternListNode(
                    PatternListQuantifier::OneOf,
                    [
                        new PatternNode([
                            new StringNode('a'),
                            new PatternListNode(
                                PatternListQuantifier::OneOf,
                                [
                                    new PatternNode([new StringNode('b')]),
                                    new PatternNode([
                                        new StringNode('c'),
                                        new PatternListNode(
                                            PatternListQuantifier::Not,
                                            [
                                                new PatternNode([new StringNode('d')]),
                                                new PatternNode([new StringNode('e')]),
                                            ],
                                        ),
                                        new StringNode('f'),
                                    ]),
                                    new PatternNode([new StringNode('g')]),
                                ],
                            ),
                            new StringNode('h'),
                        ]),
                    ],
                ),
                new Options(),
            ],
            // todo(glob-matcher): Not not
            '(empty)'    => [
                '',
                new PatternListNode(PatternListQuantifier::Not, []),
                new Options(),
            ],
        ];
    }
    // </editor-fold>
}
