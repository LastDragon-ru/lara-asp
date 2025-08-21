<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast;

use LastDragon_ru\DiyParser\Ast\Cursor;
use LastDragon_ru\GlobMatcher\Glob\Options;
use LastDragon_ru\GlobMatcher\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(GlobstarNode::class)]
final class GlobstarNodeTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderToRegex')]
    public function testToRegex(string $expected, GlobstarNode $node, Options $options): void {
        self::assertSame($expected, $node::toRegex($options, new Cursor($node)));
    }

    public function testMerge(): void {
        $a = new GlobstarNode(1);
        $b = new GlobstarNode(2);
        $c = new class(3) extends GlobstarNode {
            // empty
        };

        self::assertSame($a, GlobstarNode::merge($a, $b));
        self::assertSame(3, $a->count);

        self::assertSame($c, GlobstarNode::merge($b, $c));
        self::assertSame(2, $b->count);
        self::assertSame(3, $c->count);
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<string, array{string, GlobstarNode, Options}>
     */
    public static function dataProviderToRegex(): array {
        return [
            'default'     => [
                '(?:(?<=^|/)(?:(?!\.)(?:(?=.))[^/]*?)(?:(?:/|$)|(?=/|$)))*?',
                new GlobstarNode(),
                new Options(),
            ],
            'dot = true'  => [
                '(?:(?<=^|/)(?:(?!\.{1,2}(?:/|$))(?:(?=.))[^/]*?)(?:(?:/|$)|(?=/|$)))*?',
                new GlobstarNode(),
                new Options(hidden: true),
            ],
            'dot = false' => [
                '(?:(?<=^|/)(?:(?!\.)(?:(?=.))[^/]*?)(?:(?:/|$)|(?=/|$)))*?',
                new GlobstarNode(),
                new Options(hidden: false),
            ],
        ];
    }
    // </editor-fold>
}
