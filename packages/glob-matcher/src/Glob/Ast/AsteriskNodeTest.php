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
#[CoversClass(AsteriskNode::class)]
final class AsteriskNodeTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderToRegex')]
    public function testToRegex(string $expected, AsteriskNode $node, Options $options): void {
        self::assertSame($expected, $node::toRegex($options, new Cursor($node)));
    }

    public function testMerge(): void {
        $a = new AsteriskNode(1);
        $b = new AsteriskNode(2);
        $c = new class(3) extends AsteriskNode {
            // empty
        };

        self::assertSame($a, AsteriskNode::merge($a, $b));
        self::assertSame(3, $a->count);

        self::assertSame($c, AsteriskNode::merge($b, $c));
        self::assertSame(2, $b->count);
        self::assertSame(3, $c->count);
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<string, array{string, AsteriskNode, Options}>
     */
    public static function dataProviderToRegex(): array {
        return [
            'default' => [
                '[^/]*?',
                new AsteriskNode(),
                new Options(),
            ],
        ];
    }
    // </editor-fold>
}
