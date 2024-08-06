<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use SplFileInfo;

use function array_map;
use function basename;
use function file_get_contents;
use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(FileSystem::class)]
final class FileSystemTest extends TestCase {
    public function testGetFile(): void {
        $fs          = new FileSystem();
        $directory   = new Directory(Path::normalize(__DIR__), false);
        $readonly    = $fs->getFile($directory, __FILE__);
        $relative    = $fs->getFile($directory, basename(__FILE__));
        $notfound    = $fs->getFile($directory, 'not found');
        $writable    = new Directory(Path::normalize(__DIR__), true);
        $internal    = $fs->getFile($writable, basename(__FILE__));
        $external    = $fs->getFile($writable, '../Processor.php');
        $file        = new File(Path::normalize(__FILE__), false);
        $fromFile    = $fs->getFile($writable, $file);
        $splFile     = new SplFileInfo($file->getPath());
        $fromSplFile = $fs->getFile($writable, $splFile);

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
        $fs        = new FileSystem();
        $directory = new Directory(Path::getPath(__DIR__, '..'), false);
        $writable  = new Directory(Path::getPath(__DIR__, '..'), true);

        // Self
        self::assertSame($directory, $fs->getDirectory($directory, ''));
        self::assertSame($directory, $fs->getDirectory($directory, '.'));
        self::assertSame($directory, $fs->getDirectory($directory, $directory->getPath()));

        // Readonly
        $readonly = $fs->getDirectory($directory, __DIR__);

        self::assertNotNull($readonly);
        self::assertFalse($readonly->isWritable());
        self::assertEquals(Path::normalize(__DIR__), $readonly->getPath());

        // Relative
        $relative = $fs->getDirectory($directory, basename(__DIR__));

        self::assertNotNull($relative);
        self::assertFalse($relative->isWritable());
        self::assertEquals(Path::normalize(__DIR__), $relative->getPath());

        // Not directory
        $notDirectory = $fs->getDirectory($directory, 'not directory');

        self::assertNull($notDirectory);

        // Internal
        $internal = $fs->getDirectory($writable, basename(__DIR__));

        self::assertNotNull($internal);
        self::assertTrue($internal->isWritable());
        self::assertEquals(Path::normalize(__DIR__), $internal->getPath());

        // External
        $external = $fs->getDirectory($writable, '../Testing');

        self::assertNotNull($external);
        self::assertFalse($external->isWritable());
        self::assertEquals(Path::getPath(__DIR__, '../../Testing'), $external->getPath());

        // From file
        $fromFile = $fs->getDirectory($writable, new File(Path::normalize(__FILE__), true));

        self::assertNotNull($fromFile);
        self::assertTrue($fromFile->isWritable());
        self::assertEquals(Path::normalize(__DIR__), $fromFile->getPath());

        // From SplFileInfo
        $spl     = new SplFileInfo(__DIR__);
        $fromSpl = $fs->getDirectory($writable, $spl);

        self::assertNotNull($fromSpl);
        self::assertTrue($fromSpl->isWritable());
        self::assertEquals(Path::normalize($spl->getPathname()), $fromSpl->getPath());

        // From Directory
        $directory     = new Directory(Path::normalize(__DIR__), false);
        $fromDirectory = $fs->getDirectory($writable, $directory);

        self::assertNotNull($fromDirectory);
        self::assertFalse($directory->isWritable());
        self::assertTrue($fromDirectory->isWritable());
        self::assertEquals($directory->getPath(), $fromDirectory->getPath());
    }

    public function testGetFilesIterator(): void {
        $fs        = new FileSystem();
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
            array_map($map, iterator_to_array($fs->getFilesIterator($directory))),
        );

        self::assertEquals(
            [
                'a/a.html',
                'b/b.html',
                'c.html',
            ],
            array_map($map, iterator_to_array($fs->getFilesIterator($directory, '*.html'))),
        );

        self::assertEquals(
            [
                'c.html',
                'c.txt',
            ],
            array_map($map, iterator_to_array($fs->getFilesIterator($directory, depth: 0))),
        );

        self::assertEquals(
            [
                'c.html',
            ],
            array_map($map, iterator_to_array($fs->getFilesIterator($directory, '*.html', 0))),
        );

        self::assertEquals(
            [
                'a/a.html',
                'b/b.html',
                'c.html',
            ],
            array_map($map, iterator_to_array($fs->getFilesIterator($directory, exclude: ['#.*?\.txt$#']))),
        );
    }

    public function testGetDirectoriesIterator(): void {
        $fs        = new FileSystem();
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
            array_map($map, iterator_to_array($fs->getDirectoriesIterator($directory))),
        );

        self::assertEquals(
            [
                'a',
                'b',
            ],
            array_map($map, iterator_to_array($fs->getDirectoriesIterator($directory, depth: 0))),
        );

        self::assertEquals(
            [
                'a',
                'b',
                'b/a',
                'b/b',
            ],
            array_map($map, iterator_to_array($fs->getDirectoriesIterator($directory, exclude: '#^a/[^/]*?$#'))),
        );

        self::assertEquals(
            [
                'a',
                'a/b',
                'b',
                'b/b',
            ],
            array_map($map, iterator_to_array($fs->getDirectoriesIterator($directory, exclude: '#^[^/]*?/a$#'))),
        );
    }


    public function testSave(): void {
        $fs   = new FileSystem();
        $temp = Path::normalize(self::getTempFile(__FILE__)->getPathname());
        $file = new File($temp, true);

        self::assertTrue($fs->save($file)); // because no changes

        self::assertSame($file, $file->setContent(__METHOD__));

        self::assertTrue($fs->save($file));

        self::assertEquals(__METHOD__, file_get_contents($temp));
    }

    public function testSaveReadonly(): void {
        $fs   = new FileSystem();
        $temp = Path::normalize(self::getTempFile(__FILE__)->getPathname());
        $file = new File($temp, false);

        self::assertTrue($fs->save($file)); // because no changes

        self::assertSame($file, $file->setContent(__METHOD__));

        self::assertFalse($fs->save($file));

        self::assertEquals(__FILE__, file_get_contents($temp));
    }
}
