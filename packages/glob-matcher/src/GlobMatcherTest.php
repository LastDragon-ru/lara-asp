<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher;

use LastDragon_ru\GlobMatcher\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(GlobMatcher::class)]
final class GlobMatcherTest extends TestCase {
    public function testIsMatch(): void {
        self::assertTrue((new GlobMatcher('path/to/file'))->isMatch('path/to/file'));
        self::assertTrue((new GlobMatcher('path/to/file-{0..2}.txt'))->isMatch('path/to/file-1.txt'));
        self::assertFalse(
            (new GlobMatcher('path/to/file-{0..2}.txt', new Options(braces: false)))->isMatch('path/to/file-1.txt'),
        );
        self::assertTrue(
            (new GlobMatcher('**/file-{0..2}.txt'))->isMatch('path/to/file-1.txt'),
        );
        self::assertFalse(
            (new GlobMatcher('**/file-{0..2}.txt', new Options(globstar: false)))->isMatch('path/to/file-1.txt'),
        );
        self::assertFalse(
            (new GlobMatcher('**/*.txt'))->isMatch('path/to/.hidden.txt'),
        );
        self::assertTrue(
            (new GlobMatcher('**/*.txt', new Options(hidden: true)))->isMatch('path/to/.hidden.txt'),
        );
        self::assertFalse(
            (new GlobMatcher('\\*.txt'))->isMatch('a.txt'),
        );
        self::assertTrue(
            (new GlobMatcher('\\*.txt'))->isMatch('*.txt'),
        );
    }
}
