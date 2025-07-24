<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Ast;

use LastDragon_ru\DiyParser\Ast\Cursor;
use LastDragon_ru\GlobMatcher\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(CharacterSequenceNode::class)]
final class CharacterSequenceNodeTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param list<string> $expected
     */
    #[DataProvider('dataProviderToIterable')]
    public function testToIterable(array $expected, CharacterSequenceNode $node): void {
        self::assertSame($expected, iterator_to_array($node::toIterable(new Cursor($node)), false));
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<string, array{list<string>, CharacterSequenceNode}>
     */
    public static function dataProviderToIterable(): array {
        return [
            '-> without increment'       => [
                ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o'],
                new CharacterSequenceNode('a', 'o'),
            ],
            '-> with positive increment' => [
                ['a', 'd', 'g', 'j', 'm'],
                new CharacterSequenceNode('a', 'o', 3),
            ],
            '-> with negative increment' => [
                ['a', 'd', 'g', 'j', 'm'],
                new CharacterSequenceNode('a', 'o', -3),
            ],
            '<- without increment'       => [
                ['o', 'n', 'm', 'l', 'k', 'j', 'i', 'h', 'g', 'f', 'e', 'd', 'c', 'b', 'a'],
                new CharacterSequenceNode('o', 'a'),
            ],
            '<- with positive increment' => [
                ['o', 'l', 'i', 'f', 'c'],
                new CharacterSequenceNode('o', 'a', 3),
            ],
            '<- with negative increment' => [
                ['o', 'l', 'i', 'f', 'c'],
                new CharacterSequenceNode('o', 'a', -3),
            ],
        ];
    }
    // </editor-fold>
}
