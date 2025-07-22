<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Parser;

use LastDragon_ru\GlobMatcher\BraceExpander\Ast\BraceExpansionNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\CharacterSequenceNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\IntegerSequenceNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\SequenceNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\StringNode;
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
    public function testParse(?BraceExpansionNode $expected, string $pattern): void {
        self::assertEquals($expected, (new Parser())->parse($pattern));
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<string, array{?BraceExpansionNode, string}>
     */
    public static function dataProviderParse(): array {
        return [
            'String'                                                      => [
                new BraceExpansionNode([
                    new StringNode('string'),
                ]),
                'string',
            ],
            'Sequence'                                                    => [
                new BraceExpansionNode([
                    new SequenceNode([
                        new BraceExpansionNode([new StringNode('a')]),
                        new BraceExpansionNode([new StringNode('b')]),
                        new BraceExpansionNode([new StringNode('c')]),
                    ]),
                ]),
                '{a,b,c}',
            ],
            'Sequence with empty items'                                   => [
                new BraceExpansionNode([
                    new SequenceNode([
                        new BraceExpansionNode([]),
                        new BraceExpansionNode([new StringNode('a')]),
                        new BraceExpansionNode([]),
                        new BraceExpansionNode([new StringNode('b')]),
                        new BraceExpansionNode([]),
                        new BraceExpansionNode([new StringNode('c')]),
                        new BraceExpansionNode([]),
                    ]),
                ]),
                '{,a,,b,,c,}',
            ],
            'Sequence cannot contains only one string'                    => [
                new BraceExpansionNode([
                    new StringNode('{string}'),
                ]),
                '{string}',
            ],
            'Integer sequence: without increment'                         => [
                new BraceExpansionNode([
                    new IntegerSequenceNode('1', '5', null),
                ]),
                '{1..5}',
            ],
            'Integer sequence: with increment'                            => [
                new BraceExpansionNode([
                    new IntegerSequenceNode('1', '5', 2),
                ]),
                '{1..5..2}',
            ],
            'Integer sequence: with negative increment'                   => [
                new BraceExpansionNode([
                    new IntegerSequenceNode('1', '5', -2),
                ]),
                '{1..5..-2}',
            ],
            'Integer sequence: with zero increment'                       => [
                new BraceExpansionNode([
                    new IntegerSequenceNode('1', '5', 0),
                ]),
                '{1..5..0}',
            ],
            'Integer sequence: begins with a zero'                        => [
                new BraceExpansionNode([
                    new IntegerSequenceNode('01', '0050', 10),
                ]),
                '{01..0050..010}',
            ],
            'Integer sequence: spaces are not allowed'                    => [
                new BraceExpansionNode([
                    new StringNode('{1.. 5..2}'),
                ]),
                '{1.. 5..2}',
            ],
            'Integer sequence: increment should be integer (float)'       => [
                new BraceExpansionNode([
                    new StringNode('{1..5..0.1}'),
                ]),
                '{1..5..0.1}',
            ],
            'Integer sequence: increment should be integer (string)'      => [
                new BraceExpansionNode([
                    new StringNode('{1..5..a}'),
                ]),
                '{1..5..a}',
            ],
            'Integer sequence: negative'                                  => [
                new BraceExpansionNode([
                    new IntegerSequenceNode('-1', '-5'),
                ]),
                '{-1..-5}',
            ],
            'Integer sequence: negative with padding'                     => [
                new BraceExpansionNode([
                    new IntegerSequenceNode('-1', '-05'),
                ]),
                '{-1..-05}',
            ],
            'Character sequence: without increment'                       => [
                new BraceExpansionNode([
                    new CharacterSequenceNode('a', 'e', null),
                ]),
                '{a..e}',
            ],
            'Character sequence: with increment'                          => [
                new BraceExpansionNode([
                    new CharacterSequenceNode('a', 'e', 2),
                ]),
                '{a..e..2}',
            ],
            'Character sequence: with negative increment'                 => [
                new BraceExpansionNode([
                    new CharacterSequenceNode('a', 'e', -2),
                ]),
                '{a..e..-2}',
            ],
            'Character sequence: with zero increment'                     => [
                new BraceExpansionNode([
                    new CharacterSequenceNode('a', 'e', 0),
                ]),
                '{a..e..0}',
            ],
            'Character sequence: spaces are not allowed'                  => [
                new BraceExpansionNode([
                    new StringNode('{a.. e..2}'),
                ]),
                '{a.. e..2}',
            ],
            'Character sequence: increment should be integer (float)'     => [
                new BraceExpansionNode([
                    new StringNode('{1..5..0.1}'),
                ]),
                '{1..5..0.1}',
            ],
            'Character sequence: increment should be integer (string)'    => [
                new BraceExpansionNode([
                    new StringNode('{a..e..a}'),
                ]),
                '{a..e..a}',
            ],
            'Incremental sequence: integer and character cannot be mixed' => [
                new BraceExpansionNode([
                    new StringNode('{a..1}'),
                ]),
                '{a..1}',
            ],
            'Complex'                                                     => [
                new BraceExpansionNode([
                    new StringNode('path'),
                    new SequenceNode([
                        new BraceExpansionNode([new StringNode('/to')]),
                        new BraceExpansionNode([new StringNode('/from')]),
                        new BraceExpansionNode([]),
                    ]),
                    new StringNode('/file-'),
                    new SequenceNode([
                        new BraceExpansionNode([
                            new IntegerSequenceNode('00', '10'),
                            new StringNode('.'),
                            new SequenceNode([
                                new BraceExpansionNode([new StringNode('txt')]),
                                new BraceExpansionNode([new StringNode('js')]),
                            ]),
                        ]),
                        new BraceExpansionNode([
                            new CharacterSequenceNode('a', 'e'),
                            new StringNode('.php'),
                        ]),
                    ]),
                    new StringNode('.tmp'),
                ]),
                'path{/to,/from,}/file-{{00..10}.{txt,js},{a..e}.php}.tmp',
            ],
        ];
    }
    // </editor-fold>
}
