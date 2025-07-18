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
#[CoversClass(CharacterClassNode::class)]
final class CharacterClassNodeTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderToRegex')]
    public function testToRegex(string $expected, CharacterClassNode $node, Options $options): void {
        self::assertSame($expected, $node::toRegex($options, new Cursor($node)));
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<string, array{string, CharacterClassNode, Options}>
     */
    public static function dataProviderToRegex(): array {
        return [
            'default' => [
                '[:ascii:]',
                new CharacterClassNode(CharacterClass::Ascii),
                new Options(),
            ],
        ];
    }
    // </editor-fold>
}
