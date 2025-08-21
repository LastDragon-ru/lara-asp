<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast;

use LastDragon_ru\DiyParser\Ast\Cursor;
use LastDragon_ru\GlobMatcher\Glob\Options;
use LastDragon_ru\GlobMatcher\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Utils::class)]
final class UtilsTest extends TestCase {
    public function testToRegexZero(): void {
        $node   = new NameNode([]);
        $cursor = new Cursor($node);
        $actual = Utils::toRegex(new Options(), $cursor);

        self::assertSame('', $actual);
    }

    public function testToRegexOne(): void {
        $node   = new NameNode([new StringNode('a')]);
        $cursor = new Cursor($node);
        $actual = Utils::toRegex(new Options(), $cursor);

        self::assertSame('a', $actual);
    }

    public function testToRegexMany(): void {
        $node   = new NameNode([new StringNode('a'), new StringNode('b')]);
        $cursor = new Cursor($node);
        $actual = Utils::toRegex(new Options(), $cursor);

        self::assertSame('(?:a)(?:b)', $actual);
    }

    public function testToRegexManySeparator(): void {
        $node   = new NameNode([new StringNode('a'), new StringNode('b')]);
        $cursor = new Cursor($node);
        $actual = Utils::toRegex(new Options(), $cursor, '|');

        self::assertSame('(?:a)|(?:b)', $actual);
    }
}
