<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast\Nodes;

use LastDragon_ru\DiyParser\Ast\Cursor;
use LastDragon_ru\GlobMatcher\Glob\Options;
use LastDragon_ru\GlobMatcher\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(CharacterNode::class)]
final class CharacterNodeTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderToRegex')]
    public function testToRegex(string $expected, CharacterNode $node, Options $options): void {
        self::assertSame($expected, $node::toRegex($options, new Cursor($node)));
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<string, array{string, CharacterNode, Options}>
     */
    public static function dataProviderToRegex(): array {
        return [
            'default'          => [
                '[abc[:alpha:]]',
                new CharacterNode(
                    false,
                    [
                        new StringNode('abc'),
                        new CharacterClassNode(CharacterClass::Alpha),
                    ],
                ),
                new Options(),
            ],
            'negated'          => [
                '[^negated/]',
                new CharacterNode(
                    true,
                    [
                        new StringNode('negated'),
                    ],
                ),
                new Options(),
            ],
            'negated (with /)' => [
                '[^/negated]',
                new CharacterNode(
                    true,
                    [
                        new StringNode('/negated'),
                    ],
                ),
                new Options(),
            ],
            'range'            => [
                '[a-c[:alpha:]]',
                new CharacterNode(
                    false,
                    [
                        new StringNode('a-c'),
                        new CharacterClassNode(CharacterClass::Alpha),
                    ],
                ),
                new Options(),
            ],
            'not range'        => [
                '[a\\\\-c[:alpha:]]',
                new CharacterNode(
                    false,
                    [
                        new StringNode('a\\-c'),
                        new CharacterClassNode(CharacterClass::Alpha),
                    ],
                ),
                new Options(),
            ],
            'escape'           => [
                '[\\]\\\\\\]\\^\\\\\\^]',
                new CharacterNode(
                    false,
                    [
                        new StringNode(']\\]^\\^'),
                    ],
                ),
                new Options(),
            ],
        ];
    }
    // </editor-fold>
}
