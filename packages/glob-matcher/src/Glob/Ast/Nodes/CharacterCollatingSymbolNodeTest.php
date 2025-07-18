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
#[CoversClass(CharacterCollatingSymbolNode::class)]
final class CharacterCollatingSymbolNodeTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderToRegex')]
    public function testToRegex(string $expected, CharacterCollatingSymbolNode $node, Options $options): void {
        self::assertSame($expected, $node::toRegex($options, new Cursor($node)));
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<string, array{string, CharacterCollatingSymbolNode, Options}>
     */
    public static function dataProviderToRegex(): array {
        return [
            'default' => [
                '[.ch.]',
                new CharacterCollatingSymbolNode('ch'),
                new Options(),
            ],
        ];
    }
    // </editor-fold>
}
