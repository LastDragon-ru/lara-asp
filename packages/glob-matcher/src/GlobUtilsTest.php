<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher;

use LastDragon_ru\GlobMatcher\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(GlobUtils::class)]
final class GlobUtilsTest extends TestCase {
    public function testEscape(): void {
        self::assertSame('/a/b/c.txt', GlobUtils::escape('/a/b/c.txt'));
        self::assertSame('/a/b/\\*.txt', GlobUtils::escape('/a/b/*.txt'));
        self::assertSame('/a/\\*\\*/\\*.txt', GlobUtils::escape('/a/**/*.txt'));
        self::assertSame('\\[\\[.ch.\\]\\].txt', GlobUtils::escape('[[.ch.]].txt'));
        self::assertSame('\\[\\[=a=\\]\\].txt', GlobUtils::escape('[[=a=]].txt'));
        self::assertSame('/\\{a,b,c\\}.txt', GlobUtils::escape('/{a,b,c}.txt'));
        self::assertSame('/\\{a..c\\}.txt', GlobUtils::escape('/{a..c}.txt'));
    }
}
