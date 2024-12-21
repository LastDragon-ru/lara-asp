<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Directory::class)]
final class ItemTest extends TestCase {
    public function testConstructNotNormalized(): void {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Path must be normalized, `/../path` given.');

        new class(new DirectoryPath('/../path')) extends Item {
            // empty
        };
    }

    public function testConstructNotAbsolute(): void {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Path must be absolute, `../path` given.');

        new class((new DirectoryPath('../path'))->getNormalizedPath()) extends Item {
            // empty
        };
    }

    public function testGetRelativePath(): void {
        $path      = (new FilePath(self::getTestData()->path('a/a.txt')))->getNormalizedPath();
        $file      = new File((new FilePath(__FILE__))->getNormalizedPath());
        $parent    = new class($path->getDirectoryPath()) extends Item {
            // empty
        };
        $directory = new class((new DirectoryPath(__DIR__))->getNormalizedPath()) extends Item {
            // empty
        };

        self::assertEquals('ItemTest/a', (string) $directory->getRelativePath($parent));
        self::assertEquals('ItemTest.php', (string) $directory->getRelativePath($file));
        self::assertEquals('ItemTest/a/a.txt', (string) $directory->getRelativePath($path));
    }

    public function testIsEqual(): void {
        $path = (new FilePath(self::getTestData()->path('a/a.txt')))->getNormalizedPath();
        $a    = new class($path) extends Item {
            // empty
        };
        $b    = new class($path) extends Item {
            // empty
        };

        self::assertTrue($a->isEqual($a));
        self::assertFalse($a->isEqual($b));
    }
}
