<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\WithProcessor;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Directory::class)]
final class EntryTest extends TestCase {
    use WithProcessor;

    public function testConstructNotNormalized(): void {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Path must be normalized, `/../path` given.');

        new class(new DirectoryPath('/../path')) extends Entry {
            // empty
        };
    }

    public function testConstructNotAbsolute(): void {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Path must be absolute, `../path` given.');

        new class((new DirectoryPath('../path'))->getNormalizedPath()) extends Entry {
            // empty
        };
    }

    public function testGetRelativePath(): void {
        $fs        = $this->getFileSystem(__DIR__);
        $file      = $fs->getFile(__FILE__);
        $path      = (new FilePath(self::getTestData()->path('a/a.txt')))->getNormalizedPath();
        $parent    = new class($path->getDirectoryPath()) extends Entry {
            // empty
        };
        $directory = new class((new DirectoryPath(__DIR__))->getNormalizedPath()) extends Entry {
            // empty
        };

        self::assertSame('EntryTest/a', (string) $directory->getRelativePath($parent));
        self::assertSame('EntryTest.php', (string) $directory->getRelativePath($file));
        self::assertSame('EntryTest/a/a.txt', (string) $directory->getRelativePath($path));
    }

    public function testIsEqual(): void {
        $path = (new FilePath(self::getTestData()->path('a/a.txt')))->getNormalizedPath();
        $a    = new class($path) extends Entry {
            // empty
        };
        $b    = new class($path) extends Entry {
            // empty
        };

        self::assertTrue($a->isEqual($a));
        self::assertFalse($a->isEqual($b));
    }
}
