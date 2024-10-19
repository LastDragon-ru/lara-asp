<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function sprintf;

/**
 * @internal
 */
#[CoversClass(Directory::class)]
final class DirectoryTest extends TestCase {
    public function testConstruct(): void {
        $path      = (new DirectoryPath(__DIR__))->getNormalizedPath();
        $directory = new Directory($path, false);

        self::assertEquals($path, $directory->getPath());
        self::assertEquals((string) $path, (string) $directory->getPath());
    }

    public function testConstructNotNormalized(): void {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Path must be normalized, `/../path` given.');

        new Directory(new DirectoryPath('/../path'), false);
    }

    public function testConstructNotAbsolute(): void {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Path must be absolute, `../path` given.');

        new Directory(new DirectoryPath('../path'), false);
    }

    public function testConstructNotDirectory(): void {
        $path = (new DirectoryPath(__FILE__))->getNormalizedPath();

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(sprintf('The `%s` is not a directory.', $path));

        new Directory($path, false);
    }

    public function testIsInside(): void {
        $a         = (new FilePath(self::getTestData()->path('a/a.txt')));
        $b         = $a->getPath(new DirectoryPath('../../..'));
        $file      = new File((new FilePath(__FILE__))->getNormalizedPath(), false);
        $directory = new Directory((new DirectoryPath(__DIR__))->getNormalizedPath(), true);

        self::assertTrue($directory->isInside($a));
        self::assertFalse($directory->isInside($b));
        self::assertTrue($directory->isInside($file));
        self::assertFalse($directory->isInside($directory));
    }

    public function testGetRelativePath(): void {
        $path      = (new FilePath(self::getTestData()->path('a/a.txt')))->getNormalizedPath();
        $file      = new File((new FilePath(__FILE__))->getNormalizedPath(), false);
        $parent    = new Directory($path->getDirectoryPath(), false);
        $directory = new Directory((new DirectoryPath(__DIR__))->getNormalizedPath(), true);

        self::assertEquals('DirectoryTest/a', (string) $directory->getRelativePath($parent));
        self::assertEquals('DirectoryTest.php', (string) $directory->getRelativePath($file));
        self::assertEquals('DirectoryTest/a/a.txt', (string) $directory->getRelativePath($path));
    }
}
