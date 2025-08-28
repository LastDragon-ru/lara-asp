<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
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

        self::assertTrue($globs->isMatch(new FilePath('file.txt')));
        self::assertTrue($globs->isMatch(new FilePath('file.md')));
        self::assertFalse($globs->isMatch(new FilePath('file.php')));
        self::assertFalse($globs->isMatch(new FilePath('a/file.md')));
        self::assertTrue($globs->isMatch(new FilePath('file.tmp')));
        self::assertTrue($globs->isMatch(new FilePath('a/file.tmp')));
        self::assertTrue($globs->isMatch(new FilePath('/a/file.tmp')));
        self::assertTrue($globs->isMatch(new FilePath('a/b/file.tmp')));
        self::assertTrue($globs->isMatch(new FilePath('/a/b/file.tmp')));
    }

    public function testIsNotMatch(): void {
        $globs = new Globs(['*.txt', '*.md', '**/*.tmp']);

        self::assertFalse($globs->isNotMatch(new FilePath('file.txt')));
        self::assertFalse($globs->isNotMatch(new FilePath('file.md')));
        self::assertTrue($globs->isNotMatch(new FilePath('file.php')));
        self::assertTrue($globs->isNotMatch(new FilePath('a/file.md')));
        self::assertFalse($globs->isNotMatch(new FilePath('file.tmp')));
        self::assertFalse($globs->isNotMatch(new FilePath('a/file.tmp')));
        self::assertFalse($globs->isNotMatch(new FilePath('/a/file.tmp')));
        self::assertFalse($globs->isNotMatch(new FilePath('a/b/file.tmp')));
        self::assertFalse($globs->isNotMatch(new FilePath('/a/b/file.tmp')));
    }
}
