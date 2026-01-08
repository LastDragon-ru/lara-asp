<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast;

use LastDragon_ru\GlobMatcher\Glob\Options;
use LastDragon_ru\GlobMatcher\Package\TestCase;
use LastDragon_ru\TextParser\Ast\Cursor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(CharacterEquivalenceClassNode::class)]
final class CharacterEquivalenceClassNodeTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderToRegex')]
    public function testToRegex(string $expected, CharacterEquivalenceClassNode $node, Options $options): void {
        self::assertSame($expected, $node::toRegex($options, new Cursor($node)));
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<string, array{string, CharacterEquivalenceClassNode, Options}>
     */
    public static function dataProviderToRegex(): array {
        return [
            'default' => [
                '[=a=]',
                new CharacterEquivalenceClassNode('a'),
                new Options(),
            ],
        ];
    }
    // </editor-fold>
}
