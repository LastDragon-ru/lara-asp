<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DirectoryNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileNotFound;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\WithProcessor;
use PHPUnit\Framework\Attributes\CoversClass;

use function array_map;
use function basename;
use function file_get_contents;
use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(FileSystem::class)]
final class FileSystemTest extends TestCase {
    use WithProcessor;

    public function testGetFile(): void {
        $fs           = $this->getFileSystem(__DIR__);
        $path         = (new FilePath(self::getTestData()->path('c.txt')))->getNormalizedPath();
        $file         = new File($path);
        $readonly     = $fs->getFile(__FILE__);
        $relative     = $fs->getFile(basename(__FILE__));
        $internal     = $fs->getFile(self::getTestData()->path('c.html'));
        $external     = $fs->getFile('../Processor.php');
        $fromFilePath = $fs->getFile($path);

        self::assertEquals(
            (string) (new FilePath(__FILE__))->getNormalizedPath(),
            (string) $readonly,
        );

        self::assertEquals(
            (string) (new FilePath(__FILE__))->getNormalizedPath(),
            (string) $relative,
        );

        self::assertEquals(
            (string) (new FilePath(self::getTestData()->path('c.html')))->getNormalizedPath(),
            (string) $internal,
        );

        self::assertEquals(
            (string) (new FilePath(__FILE__))->getFilePath('../Processor.php'),
            (string) $external,
        );

        self::assertEquals($file->getPath(), $fromFilePath->getPath());
        self::assertEquals(
            (string) (new FilePath(self::getTestData()->path('c.txt')))->getNormalizedPath(),
            (string) $fromFilePath,
        );
    }

    public function testGetFileNotFound(): void {
        self::expectException(FileNotFound::class);

        (new FileSystem((new DirectoryPath(__DIR__))->getNormalizedPath()))->getFile('not found');
    }

    public function testGetDirectory(): void {
        // Prepare
        $fs = $this->getFileSystem(__DIR__.'/..');

        // Self
        self::assertSame(
            $fs->getDirectory('.'),
            $fs->getDirectory(''),
        );

        // Readonly
        $readonly = $fs->getDirectory(__DIR__);

        self::assertEquals(
            (string) (new DirectoryPath(__DIR__))->getNormalizedPath(),
            (string) $readonly,
        );

        // Relative
        $relative = $fs->getDirectory(basename(__DIR__));

        self::assertEquals(
            (string) (new DirectoryPath(__DIR__))->getNormalizedPath(),
            (string) $relative,
        );

        // Internal
        $internalPath = self::getTestData()->path('a');
        $internal     = $fs->getDirectory($internalPath);

        self::assertEquals($internalPath, (string) $internal);

        // External
        $external = $fs->getDirectory('../Testing');

        self::assertEquals(
            (string) (new DirectoryPath(__DIR__))->getDirectoryPath('../../Testing'),
            (string) $external,
        );

        // From FilePath
        $filePath     = (new FilePath(self::getTestData()->path('c.html')))->getNormalizedPath();
        $fromFilePath = $fs->getDirectory($filePath);

        self::assertEquals(
            (string) (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath(),
            (string) $fromFilePath,
        );

        // From DirectoryPath
        $directoryPath     = (new DirectoryPath(self::getTestData()->path('a/a')))->getNormalizedPath();
        $fromDirectoryPath = $fs->getDirectory($directoryPath);

        self::assertEquals((string) $directoryPath, (string) $fromDirectoryPath);
    }

    public function testGetDirectoryNotFound(): void {
        self::expectException(DirectoryNotFound::class);

        (new FileSystem((new DirectoryPath(__DIR__))->getNormalizedPath()))->getDirectory('not found');
    }

    public function testGetFilesIterator(): void {
        $input      = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $directory  = new Directory($input);
        $filesystem = $this->getFileSystem($input);
        $map        = static function (File $file) use ($directory): string {
            return (string) $directory->getRelativePath($file);
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
            array_map($map, iterator_to_array($filesystem->getFilesIterator($directory))),
        );

        self::assertEquals(
            [
                'a/a.html',
                'b/b.html',
                'c.html',
            ],
            array_map($map, iterator_to_array($filesystem->getFilesIterator($directory, '*.html'))),
        );

        self::assertEquals(
            [
                'c.html',
                'c.txt',
            ],
            array_map($map, iterator_to_array($filesystem->getFilesIterator($directory, depth: 0))),
        );

        self::assertEquals(
            [
                'c.html',
            ],
            array_map($map, iterator_to_array($filesystem->getFilesIterator($directory, '*.html', 0))),
        );

        self::assertEquals(
            [
                'a/a.html',
                'b/b.html',
                'c.html',
            ],
            array_map($map, iterator_to_array($filesystem->getFilesIterator($directory, exclude: ['#.*?\.txt$#']))),
        );
    }

    public function testGetDirectoriesIterator(): void {
        $input      = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $directory  = new Directory($input);
        $filesystem = $this->getFileSystem($input);
        $map        = static function (Directory $dir) use ($directory): string {
            return (string) $directory->getRelativePath($dir);
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
            array_map($map, iterator_to_array($filesystem->getDirectoriesIterator($directory))),
        );

        self::assertEquals(
            [
                'a',
                'b',
            ],
            array_map($map, iterator_to_array($filesystem->getDirectoriesIterator($directory, depth: 0))),
        );

        self::assertEquals(
            [
                'a',
                'b',
                'b/a',
                'b/b',
            ],
            array_map(
                $map,
                iterator_to_array($filesystem->getDirectoriesIterator($directory, exclude: '#^a/[^/]*?$#')),
            ),
        );

        self::assertEquals(
            [
                'a',
                'a/b',
                'b',
                'b/b',
            ],
            array_map(
                $map,
                iterator_to_array($filesystem->getDirectoriesIterator($directory, exclude: '#^[^/]*?/a$#')),
            ),
        );
    }

    public function testSaveInsideRoot(): void {
        $temp = (new FilePath(self::getTempFile(__FILE__)->getPathname()))->getNormalizedPath();
        $file = new File($temp);
        $fs   = $this->getFileSystem($temp->getDirectoryPath());

        self::assertTrue($fs->save($file)); // because no changes

        self::assertSame($file, $file->setContent(__METHOD__));

        self::assertTrue($fs->save($file));

        self::assertEquals(__METHOD__, file_get_contents((string) $temp));
    }

    public function testSaveOutsideRoot(): void {
        $fs   = $this->getFileSystem(__DIR__);
        $temp = (new FilePath(self::getTempFile(__FILE__)->getPathname()))->getNormalizedPath();
        $file = new File($temp);

        self::assertTrue($fs->save($file)); // because no changes

        self::assertSame($file, $file->setContent(__METHOD__));

        self::assertFalse($fs->save($file));

        self::assertEquals(__FILE__, file_get_contents((string) $temp));
    }

    public function testCache(): void {
        $fs        = $this->getFileSystem(__DIR__);
        $file      = $fs->getFile(__FILE__);
        $directory = $fs->getDirectory(__DIR__);

        self::assertSame($file, $fs->getFile(__FILE__));

        self::assertSame($directory, $fs->getDirectory(__DIR__));
    }
}
