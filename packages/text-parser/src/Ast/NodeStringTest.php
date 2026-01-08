<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Ast;

use LastDragon_ru\TextParser\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(NodeString::class)]
final class NodeStringTest extends TestCase {
    public function testMerge(): void {
        $a = new NodeString('1');
        $b = new NodeString('2');
        $c = new class('3') extends NodeString {
            // empty
        };

        self::assertSame($a, NodeString::merge($a, $b));
        self::assertSame('12', $a->string);

        self::assertSame($c, NodeString::merge($b, $c));
        self::assertSame('2', $b->string);
        self::assertSame('3', $c->string);
    }
}
