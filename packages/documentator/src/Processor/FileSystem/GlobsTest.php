<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Globs::class)]
final class GlobsTest extends TestCase {
    public function testIsEmpty(): void {
        self::assertFalse((new Globs(['*.txt']))->isEmpty());
        self::assertTrue((new Globs(['']))->isEmpty());
    }

    public function testIsMatch(): void {
        $globs = new Globs(['*.txt', '*.md', '**/*.tmp']);

        self::assertTrue($globs->isMatch('file.txt'));
        self::assertTrue($globs->isMatch('file.md'));
        self::assertFalse($globs->isMatch('file.php'));
        self::assertFalse($globs->isMatch('a/file.md'));
        self::assertTrue($globs->isMatch('file.tmp'));
        self::assertTrue($globs->isMatch('a/file.tmp'));
        self::assertTrue($globs->isMatch('/a/file.tmp'));
        self::assertTrue($globs->isMatch('a/b/file.tmp'));
        self::assertTrue($globs->isMatch('/a/b/file.tmp'));
    }

    public function testIsNotMatch(): void {
        $globs = new Globs(['*.txt', '*.md', '**/*.tmp']);

        self::assertFalse($globs->isNotMatch('file.txt'));
        self::assertFalse($globs->isNotMatch('file.md'));
        self::assertTrue($globs->isNotMatch('file.php'));
        self::assertTrue($globs->isNotMatch('a/file.md'));
        self::assertFalse($globs->isNotMatch('file.tmp'));
        self::assertFalse($globs->isNotMatch('a/file.tmp'));
        self::assertFalse($globs->isNotMatch('/a/file.tmp'));
        self::assertFalse($globs->isNotMatch('a/b/file.tmp'));
        self::assertFalse($globs->isNotMatch('/a/b/file.tmp'));
    }
}
