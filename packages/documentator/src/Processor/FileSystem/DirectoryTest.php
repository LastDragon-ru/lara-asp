<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use SplFileInfo;

use function array_map;
use function basename;
use function dirname;
use function iterator_to_array;
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

    public function testGetFile(): void {
        $directory   = new Directory(Path::normalize(__DIR__), false);
        $readonly    = $directory->getFile(__FILE__);
        $relative    = $directory->getFile(basename(__FILE__));
        $notfound    = $directory->getFile('not found');
        $writable    = new Directory(Path::normalize(__DIR__), true);
        $internal    = $writable->getFile(basename(__FILE__));
        $external    = $writable->getFile('../Processor.php');
        $file        = new File(Path::normalize(__FILE__), false);
        $fromFile    = $writable->getFile($file);
        $splFile     = new SplFileInfo($file->getPath());
        $fromSplFile = $writable->getFile($splFile);

        self::assertNotNull($readonly);
        self::assertFalse($readonly->isWritable());
        self::assertEquals(Path::normalize(__FILE__), $readonly->getPath());

        self::assertNotNull($relative);
        self::assertFalse($relative->isWritable());
        self::assertEquals(Path::normalize(__FILE__), $relative->getPath());

        self::assertNull($notfound);

        self::assertNotNull($internal);
        self::assertTrue($internal->isWritable());
        self::assertEquals(Path::normalize(__FILE__), $internal->getPath());

        self::assertNotNull($external);
        self::assertFalse($external->isWritable());
        self::assertEquals(Path::getPath(__FILE__, '../Processor.php'), $external->getPath());

        self::assertNotNull($fromFile);
        self::assertFalse($file->isWritable());
        self::assertTrue($fromFile->isWritable());
        self::assertEquals($file->getPath(), $fromFile->getPath());
        self::assertEquals(Path::normalize(__FILE__), $fromFile->getPath());

        self::assertNotNull($fromSplFile);
        self::assertFalse($file->isWritable());
        self::assertTrue($fromSplFile->isWritable());
        self::assertEquals($file->getPath(), $fromSplFile->getPath());
        self::assertEquals(Path::normalize(__FILE__), $fromSplFile->getPath());
    }

    public function testGetDirectory(): void {
        // Prepare
        $directory = new Directory(Path::getPath(__DIR__, '..'), false);
        $writable  = new Directory(Path::getPath(__DIR__, '..'), true);

        // Self
        self::assertSame($directory, $directory->getDirectory(''));
        self::assertSame($directory, $directory->getDirectory('.'));
        self::assertSame($directory, $directory->getDirectory($directory->getPath()));

        // Readonly
        $readonly = $directory->getDirectory(__DIR__);

        self::assertNotNull($readonly);
        self::assertFalse($readonly->isWritable());
        self::assertEquals(Path::normalize(__DIR__), $readonly->getPath());

        // Relative
        $relative = $directory->getDirectory(basename(__DIR__));

        self::assertNotNull($relative);
        self::assertFalse($relative->isWritable());
        self::assertEquals(Path::normalize(__DIR__), $relative->getPath());

        // Not directory
        $notDirectory = $directory->getDirectory('not directory');

        self::assertNull($notDirectory);

        // Internal
        $internal = $writable->getDirectory(basename(__DIR__));

        self::assertNotNull($internal);
        self::assertTrue($internal->isWritable());
        self::assertEquals(Path::normalize(__DIR__), $internal->getPath());

        // External
        $external = $writable->getDirectory('../Testing');

        self::assertNotNull($external);
        self::assertFalse($external->isWritable());
        self::assertEquals(Path::getPath(__DIR__, '../../Testing'), $external->getPath());

        // From file
        $fromFile = $writable->getDirectory(new File(Path::normalize(__FILE__), true));

        self::assertNotNull($fromFile);
        self::assertTrue($fromFile->isWritable());
        self::assertEquals(Path::normalize(__DIR__), $fromFile->getPath());

        // From SplFileInfo
        $spl     = new SplFileInfo(__DIR__);
        $fromSpl = $writable->getDirectory($spl);

        self::assertNotNull($fromSpl);
        self::assertTrue($fromSpl->isWritable());
        self::assertEquals(Path::normalize($spl->getPathname()), $fromSpl->getPath());

        // From Directory
        $directory     = new Directory(Path::normalize(__DIR__), false);
        $fromDirectory = $writable->getDirectory($directory);

        self::assertNotNull($fromDirectory);
        self::assertFalse($directory->isWritable());
        self::assertTrue($fromDirectory->isWritable());
        self::assertEquals($directory->getPath(), $fromDirectory->getPath());
    }

    public function testGetFilesIterator(): void {
        $root      = Path::normalize(self::getTestData()->path(''));
        $directory = new Directory($root, false);
        $map       = static function (File $file) use ($root): string {
            return Path::getRelativePath($root, $file->getPath());
        };

        self::assertEquals(
            [
                'a/a.html',
                'a/a.txt',
                'a/a/aa.txt',
                'a/b/ab.txt',
                'b/a/ba.txt',
                'b/b.html',
                'b/b.txt',
                'b/b/bb.txt',
                'c.html',
                'c.txt',
            ],
            array_map($map, iterator_to_array($directory->getFilesIterator())),
        );

        self::assertEquals(
            [
                'a/a.html',
                'b/b.html',
                'c.html',
            ],
            array_map($map, iterator_to_array($directory->getFilesIterator('*.html'))),
        );

        self::assertEquals(
            [
                'c.html',
                'c.txt',
            ],
            array_map($map, iterator_to_array($directory->getFilesIterator(depth: 0))),
        );

        self::assertEquals(
            [
                'c.html',
            ],
            array_map($map, iterator_to_array($directory->getFilesIterator('*.html', 0))),
        );
    }

    public function testGetDirectoriesIterator(): void {
        $root      = Path::normalize(self::getTestData()->path(''));
        $directory = new Directory($root, false);
        $map       = static function (Directory $directory) use ($root): string {
            return Path::getRelativePath($root, $directory->getPath());
        };

        self::assertEquals(
            [
                'a',
                'a/a',
                'a/b',
                'b',
                'b/a',
                'b/b',
            ],
            array_map($map, iterator_to_array($directory->getDirectoriesIterator())),
        );

        self::assertEquals(
            [
                'a',
                'b',
            ],
            array_map($map, iterator_to_array($directory->getDirectoriesIterator(depth: 0))),
        );
    }

    public function testGetRelativePath(): void {
        $path      = Path::normalize(self::getTestData()->path('a/a.txt'));
        $file      = new File(Path::normalize(__FILE__), false);
        $internal  = new Directory(dirname($path), false);
        $directory = new Directory(Path::normalize(__DIR__), true);

        self::assertEquals('DirectoryTest/a', $internal->getRelativePath($directory));
        self::assertEquals('DirectoryTest/a', $internal->getRelativePath($file));
    }
}
