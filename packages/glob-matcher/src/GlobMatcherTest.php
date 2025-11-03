<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher;

use LastDragon_ru\GlobMatcher\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(GlobMatcher::class)]
final class GlobMatcherTest extends TestCase {
    public function testMatch(): void {
        self::assertTrue((new GlobMatcher('path/to/file'))->match('path/to/file'));
        self::assertTrue((new GlobMatcher('path/to/file-{0..2}.txt'))->match('path/to/file-1.txt'));
        self::assertFalse(
            (new GlobMatcher('path/to/file-{0..2}.txt', new Options(braces: false)))->match('path/to/file-1.txt'),
        );
        self::assertTrue(
            (new GlobMatcher('**/file-{0..2}.txt'))->match('path/to/file-1.txt'),
        );
        self::assertFalse(
            (new GlobMatcher('**/file-{0..2}.txt', new Options(globstar: false)))->match('path/to/file-1.txt'),
        );
        self::assertFalse(
            (new GlobMatcher('**/*.txt'))->match('path/to/.hidden.txt'),
        );
        self::assertTrue(
            (new GlobMatcher('**/*.txt', new Options(hidden: true)))->match('path/to/.hidden.txt'),
        );
        self::assertFalse(
            (new GlobMatcher('\\*.txt'))->match('a.txt'),
        );
        self::assertTrue(
            (new GlobMatcher('\\*.txt'))->match('*.txt'),
        );
    }
}
