<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Ast\Nodes;

use LastDragon_ru\GlobMatcher\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(AsteriskNode::class)]
final class AsteriskNodeTest extends TestCase {
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
}
