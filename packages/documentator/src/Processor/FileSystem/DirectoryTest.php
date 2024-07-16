<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use SplFileInfo;

use function basename;
use function dirname;
use function sprintf;

/**
 * @internal
 */
#[CoversClass(Directory::class)]
final class DirectoryTest extends TestCase {
    public function testConstruct(): void {
        $path      = Path::normalize(__DIR__);
        $directory = new Directory($path, false);

        self::assertEquals($path, $directory->getPath());
        self::assertEquals("{$path}", $directory->getPath());
    }

    public function testConstructNotNormalized(): void {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Path must be normalized, `/../path` given.');

        new Directory('/../path', false);
    }

    public function testConstructNotAbsolute(): void {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Path must be absolute, `../path` given.');

        new Directory('../path', false);
    }

    public function testConstructNotDirectory(): void {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(sprintf('The `%s` is not a directory.', Path::normalize(__FILE__)));

        new Directory(Path::normalize(__FILE__), false);
    }

    public function testIsInside(): void {
        $path         = __FILE__;
        $file         = new File(Path::normalize($path), false);
        $splFile      = new SplFileInfo($path);
        $directory    = new Directory(Path::normalize(__DIR__), true);
        $splDirectory = new SplFileInfo(Path::join(__DIR__, 'abc'));

        self::assertTrue($directory->isInside($path));
        self::assertTrue($directory->isInside($file));
        self::assertFalse($directory->isInside(__DIR__));
        self::assertFalse($directory->isInside($directory));
        self::assertTrue($directory->isInside('./file.txt'));
        self::assertFalse($directory->isInside('./../file.txt'));
        self::assertTrue($directory->isInside('./path/../file.txt'));
        self::assertTrue($directory->isInside($splFile));
        self::assertTrue($directory->isInside($splDirectory));
    }

    public function testGetRelativePath(): void {
        $path      = Path::normalize(self::getTestData()->path('a/a.txt'));
        $file      = new File(Path::normalize(__FILE__), false);
        $internal  = new Directory(dirname($path), false);
        $directory = new Directory(Path::normalize(__DIR__), true);

        self::assertEquals('DirectoryTest/a', $internal->getRelativePath($directory));
        self::assertEquals('DirectoryTest/a', $internal->getRelativePath($file));
    }

    public function testGetPath(): void {
        $file      = Path::normalize(__FILE__);
        $path      = Path::normalize(__DIR__);
        $directory = new Directory($path, true);

        self::assertEquals($path, $directory->getPath());
        self::assertEquals($file, $directory->getPath($file));
        self::assertEquals($file, $directory->getPath(basename($file)));
    }
}
