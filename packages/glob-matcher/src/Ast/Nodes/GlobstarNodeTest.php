<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Ast\Nodes;

use LastDragon_ru\GlobMatcher\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(GlobstarNode::class)]
final class GlobstarNodeTest extends TestCase {
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
}
