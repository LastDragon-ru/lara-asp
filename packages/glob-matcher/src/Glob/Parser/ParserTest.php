<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Parser;

use LastDragon_ru\GlobMatcher\Glob\Ast\AsteriskNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\CharacterClass;
use LastDragon_ru\GlobMatcher\Glob\Ast\CharacterClassNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\CharacterCollatingSymbolNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\CharacterEquivalenceClassNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\CharacterNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\GlobNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\GlobstarNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\NameNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\PatternListNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\PatternListQuantifier;
use LastDragon_ru\GlobMatcher\Glob\Ast\PatternNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\QuestionNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\SegmentNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\StringNode;
use LastDragon_ru\GlobMatcher\Glob\Options;
use LastDragon_ru\GlobMatcher\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(Parser::class)]
final class ParserTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderParse')]
    public function testParse(?GlobNode $expected, Options $options, string $pattern): void {
        self::assertEquals($expected, (new Parser($options))->parse($pattern));
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<string, array{?GlobNode, Options, string}>
     */
    public static function dataProviderParse(): array {
        return [
            'string'                                     => [
                new GlobNode([
                    new NameNode([
                        new StringNode('string'),
                    ]),
                ]),
                new Options(),
                'string',
            ],
            '*'                                          => [
                new GlobNode([
                    new NameNode([
                        new AsteriskNode(),
                        new StringNode('.jpg'),
                    ]),
                ]),
                new Options(),
                '*.jpg',
            ],
            '* should be merged if multiple'             => [
                new GlobNode([
                    new NameNode([
                        new AsteriskNode(4),
                    ]),
                    new SegmentNode(),
                    new NameNode([
                        new AsteriskNode(2),
                    ]),
                    new SegmentNode(),
                    new NameNode([
                        new AsteriskNode(2),
                        new StringNode('.jpg'),
                    ]),
                ]),
                new Options(
                    globstar: false,
                ),
                '****/**/**.jpg',
            ],
            '.*'                                         => [
                new GlobNode([
                    new NameNode([
                        new StringNode('.'),
                        new AsteriskNode(),
                    ]),
                ]),
                new Options(),
                '.*',
            ],
            'a.*'                                        => [
                new GlobNode([
                    new NameNode([
                        new StringNode('a.'),
                        new AsteriskNode(),
                    ]),
                ]),
                new Options(),
                'a.*',
            ],
            '**/*.jpg'                                   => [
                new GlobNode([
                    new GlobstarNode(),
                    new NameNode([
                        new AsteriskNode(),
                        new StringNode('.jpg'),
                    ]),
                ]),
                new Options(
                    globstar: true,
                ),
                '**/*.jpg',
            ],
            '/**'                                        => [
                new GlobNode([
                    new SegmentNode(),
                    new GlobstarNode(),
                ]),
                new Options(
                    globstar: true,
                ),
                '/**',
            ],
            '**'                                         => [
                new GlobNode([
                    new GlobstarNode(),
                ]),
                new Options(
                    globstar: true,
                ),
                '**',
            ],
            '**/'                                        => [
                new GlobNode([
                    new GlobstarNode(),
                    new SegmentNode(),
                ]),
                new Options(
                    globstar: true,
                ),
                '**/',
            ],
            '/**/'                                       => [
                new GlobNode([
                    new SegmentNode(),
                    new GlobstarNode(),
                    new SegmentNode(),
                ]),
                new Options(
                    globstar: true,
                ),
                '/**/',
            ],
            '**/.abc/**'                                 => [
                new GlobNode([
                    new GlobstarNode(),
                    new NameNode([
                        new StringNode('.abc'),
                    ]),
                    new SegmentNode(),
                    new GlobstarNode(),
                ]),
                new Options(
                    globstar: true,
                ),
                '**/.abc/**',
            ],
            '** should be merged if multiple'            => [
                new GlobNode([
                    new GlobstarNode(3),
                    new NameNode([
                        new AsteriskNode(),
                        new StringNode('.jpg'),
                    ]),
                ]),
                new Options(
                    globstar: true,
                ),
                '**/**/**/*.jpg',
            ],
            '/**/ should be merged if multiple'          => [
                new GlobNode([
                    new SegmentNode(),
                    new GlobstarNode(3),
                    new SegmentNode(),
                ]),
                new Options(
                    globstar: true,
                ),
                '/**/**/**/',
            ],
            '/** should be merged if multiple'           => [
                new GlobNode([
                    new SegmentNode(),
                    new GlobstarNode(3),
                ]),
                new Options(
                    globstar: true,
                ),
                '/**/**/**',
            ],
            '**/ should be merged if multiple'           => [
                new GlobNode([
                    new GlobstarNode(3),
                    new SegmentNode(),
                ]),
                new Options(
                    globstar: true,
                ),
                '**/**/**/',
            ],
            '** is asterisk if not preceded by / or ^'   => [
                new GlobNode([
                    new NameNode([
                        new StringNode('a'),
                    ]),
                    new SegmentNode(),
                    new NameNode([
                        new StringNode('b'),
                        new AsteriskNode(2),
                    ]),
                    new SegmentNode(),
                    new NameNode([
                        new AsteriskNode(),
                        new StringNode('.js'),
                    ]),
                ]),
                new Options(),
                'a/b**/*.js',
            ],
            '?'                                          => [
                new GlobNode([
                    new NameNode([
                        new QuestionNode(),
                        new StringNode('at.png'),
                    ]),
                ]),
                new Options(),
                '?at.png',
            ],
            'characters'                                 => [
                new GlobNode([
                    new NameNode([
                        new CharacterNode(false, [
                            new StringNode('abc'),
                        ]),
                    ]),
                ]),
                new Options(),
                '[abc]',
            ],
            '!characters'                                => [
                new GlobNode([
                    new NameNode([
                        new CharacterNode(true, [
                            new StringNode('abc'),
                        ]),
                    ]),
                ]),
                new Options(),
                '[!abc]',
            ],
            '^characters'                                => [
                new GlobNode([
                    new NameNode([
                        new CharacterNode(true, [
                            new StringNode('abc'),
                        ]),
                    ]),
                ]),
                new Options(),
                '[^abc]',
            ],
            '^ inside characters'                        => [
                new GlobNode([
                    new NameNode([
                        new CharacterNode(true, [
                            new StringNode('^abc'),
                        ]),
                    ]),
                ]),
                new Options(),
                '[^^abc]',
            ],
            'characters invalid'                         => [
                new GlobNode([
                    new NameNode([
                        new StringNode('[]abc'),
                    ]),
                ]),
                new Options(),
                '[]abc',
            ],
            'characters escaped'                         => [
                new GlobNode([
                    new NameNode([
                        new CharacterNode(false, [
                            new StringNode('a]bc'),
                        ]),
                    ]),
                ]),
                new Options(),
                '[a\\]bc]',
            ],
            'characters can not be empty'                => [
                new GlobNode([
                    new NameNode([
                        new CharacterNode(false, [
                            new StringNode(']abc'),
                        ]),
                    ]),
                ]),
                new Options(),
                '[]abc]',
            ],
            '^characters can not be empty'               => [
                new GlobNode([
                    new NameNode([
                        new CharacterNode(true, [
                            new StringNode(']abc'),
                        ]),
                    ]),
                ]),
                new Options(),
                '[^]abc]',
            ],
            '!characters can not be empty'               => [
                new GlobNode([
                    new NameNode([
                        new CharacterNode(true, [
                            new StringNode(']abc'),
                        ]),
                    ]),
                ]),
                new Options(),
                '[!]abc]',
            ],
            'character class valid'                      => [
                new GlobNode([
                    new NameNode([
                        new CharacterNode(false, [
                            new CharacterClassNode(CharacterClass::Alpha),
                        ]),
                    ]),
                ]),
                new Options(),
                '[[:alpha:]]',
            ],
            'character class invalid'                    => [
                new GlobNode([
                    new NameNode([
                        new CharacterNode(false, [
                            new StringNode('[:abc:'),
                        ]),
                        new StringNode(']'),
                    ]),
                ]),
                new Options(),
                '[[:abc:]]',
            ],
            'character class outside characters'         => [
                new GlobNode([
                    new NameNode([
                        new CharacterNode(false, [
                            new StringNode(':alpha:'),
                        ]),
                    ]),
                ]),
                new Options(),
                '[:alpha:]',
            ],
            'character collating symbol'                 => [
                new GlobNode([
                    new NameNode([
                        new CharacterNode(false, [
                            new CharacterCollatingSymbolNode('ch'),
                        ]),
                    ]),
                ]),
                new Options(),
                '[[.ch.]]',
            ],
            'character equivalence class'                => [
                new GlobNode([
                    new NameNode([
                        new CharacterNode(false, [
                            new CharacterEquivalenceClassNode('a'),
                        ]),
                    ]),
                ]),
                new Options(),
                '[[=a=]]',
            ],
            '/./'                                        => [
                new GlobNode([
                    new SegmentNode(),
                    new NameNode([
                        new StringNode('.'),
                    ]),
                    new SegmentNode(),
                ]),
                new Options(),
                '/./',
            ],
            '[[:unknown:]'                               => [
                new GlobNode([
                    new NameNode([
                        new CharacterNode(false, [
                            new StringNode('[:unknown:'),
                        ]),
                    ]),
                ]),
                new Options(),
                '[[:unknown:]',
            ],
            '[[:alpha:]'                                 => [
                new GlobNode([
                    new NameNode([
                        new CharacterNode(false, [
                            new StringNode('[:alpha:'),
                        ]),
                    ]),
                ]),
                new Options(),
                '[[:alpha:]',
            ],
            '[^'                                         => [
                new GlobNode([
                    new NameNode([
                        new StringNode('[^'),
                    ]),
                ]),
                new Options(),
                '[^',
            ],
            'extglob = off'                              => [
                new GlobNode([new NameNode([new StringNode('+(a|b|c)')])]),
                new Options(extended: false),
                '+(a|b|c)',
            ],
            'extglob'                                    => [
                new GlobNode([
                    new SegmentNode(),
                    new NameNode([
                        new StringNode('a'),
                    ]),
                    new SegmentNode(),
                    new NameNode([
                        new PatternListNode(
                            PatternListQuantifier::OneOrMore,
                            [
                                new PatternNode([
                                    new StringNode('b'),
                                ]),
                                new PatternNode([
                                    new StringNode('c'),
                                ]),
                                new PatternNode([
                                    new PatternListNode(
                                        PatternListQuantifier::ZeroOrMore,
                                        [
                                            new PatternNode([
                                                new StringNode('d'),
                                            ]),
                                            new PatternNode([
                                                new StringNode('e'),
                                            ]),
                                        ],
                                    ),
                                ]),
                                new PatternNode([
                                    new PatternListNode(
                                        PatternListQuantifier::ZeroOrOne,
                                        [
                                            new PatternNode([
                                                new StringNode('f'),
                                            ]),
                                        ],
                                    ),
                                ]),
                            ],
                        ),
                    ]),
                ]),
                new Options(),
                '/a/+(b|c|*(d|e)|?(f))',
            ],
            'extglob (empty)'                            => [
                new GlobNode([
                    new NameNode([
                        new PatternListNode(
                            PatternListQuantifier::OneOrMore,
                            [
                                // empty
                            ],
                        ),
                    ]),
                ]),
                new Options(),
                '+()',
            ],
            'extglob (unclosed)'                         => [
                new GlobNode([
                    new NameNode([
                        new StringNode('+(b|'),
                        new PatternListNode(
                            PatternListQuantifier::ZeroOrMore,
                            [
                                new PatternNode([
                                    new StringNode('d'),
                                ]),
                            ],
                        ),
                    ]),
                ]),
                new Options(),
                '+(b|*(d)',
            ],
            'extglob cannot contain `/`'                 => [
                new GlobNode([
                    new NameNode([
                        new StringNode('@(a'),
                    ]),
                    new SegmentNode(),
                    new NameNode([
                        new StringNode('b|c'),
                    ]),
                    new SegmentNode(),
                    new NameNode([
                        new StringNode('d)'),
                    ]),
                ]),
                new Options(),
                '@(a/b|c\\/d)',
            ],
            '(...)*'                                     => [
                new GlobNode([
                    new NameNode([
                        new StringNode('(a+|b)'),
                        new AsteriskNode(),
                    ]),
                ]),
                new Options(),
                '(a+|b)*',
            ],
            '`/` cannot be inside file name and escaped' => [
                new GlobNode([
                    new NameNode([
                        new StringNode('a'),
                    ]),
                    new SegmentNode(),
                    new NameNode([
                        new StringNode('b'),
                    ]),
                    new SegmentNode(),
                    new SegmentNode(),
                    new NameNode([
                        new StringNode('[c'),
                    ]),
                    new SegmentNode(),
                    new NameNode([
                        new StringNode('d]'),
                    ]),
                    new SegmentNode(),
                    new NameNode([
                        new StringNode('[e'),
                    ]),
                    new SegmentNode(),
                    new NameNode([
                        new StringNode('f]'),
                    ]),
                    new SegmentNode(),
                    new GlobstarNode(),
                ]),
                new Options(),
                'a\\/b//[c/d]/[e\\/f]\\/**',
            ],
            'x**(x)'                                     => [
                new GlobNode([
                    new NameNode([
                        new StringNode('x'),
                        new AsteriskNode(),
                        new PatternListNode(
                            PatternListQuantifier::ZeroOrMore,
                            [
                                new PatternNode([
                                    new StringNode('x'),
                                ]),
                            ],
                        ),
                    ]),
                ]),
                new Options(),
                'x**(x)',
            ],
            'x***(x)'                                    => [
                new GlobNode([
                    new NameNode([
                        new StringNode('x'),
                        new AsteriskNode(2),
                        new PatternListNode(
                            PatternListQuantifier::ZeroOrMore,
                            [
                                new PatternNode([
                                    new StringNode('x'),
                                ]),
                            ],
                        ),
                    ]),
                ]),
                new Options(),
                'x***(x)',
            ],
        ];
    }
    // </editor-fold>
}
