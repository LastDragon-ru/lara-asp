<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithProcessor;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use PHPUnit\Framework\Attributes\CoversClass;

use function array_map;
use function basename;
use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(FileIterator::class)]
final class FileIteratorTest extends TestCase {
    use WithProcessor;

    public function testGetPath(): void {
        $filesystem = $this->getFileSystem(__DIR__);
        $directory  = $filesystem->getDirectory(__DIR__);
        $path       = $directory->getPath();

        self::assertSame('path/to/directory', (string) (new FileIterator('path/to/directory'))->getPath($filesystem));
        self::assertSame((string) $directory, (string) (new FileIterator($directory))->getPath($filesystem));
        self::assertSame((string) $path, (string) (new FileIterator($path))->getPath($filesystem));
    }

    public function testInvoke(): void {
        $fs        = $this->getFileSystem(__DIR__);
        $path      = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $include   = '**/*.txt';
        $absolute  = new FileIterator($path, $include);
        $relative  = new FileIterator(basename((string) $path), $include);
        $directory = new FileIterator($fs->getDirectory($path), $include);
        $formatter = static function (File|FilePath $item): string {
            return (string) $item;
        };
        $expected  = [
            (string) $fs->input->getDirectoryPath('FileIteratorTest/a/a.txt'),
            (string) $fs->input->getDirectoryPath('FileIteratorTest/a/a/aa.txt'),
            (string) $fs->input->getDirectoryPath('FileIteratorTest/a/b/ab.txt'),
            (string) $fs->input->getDirectoryPath('FileIteratorTest/b/a/ba.txt'),
            (string) $fs->input->getDirectoryPath('FileIteratorTest/b/b.txt'),
            (string) $fs->input->getDirectoryPath('FileIteratorTest/b/b/bb.txt'),
            (string) $fs->input->getDirectoryPath('FileIteratorTest/c.txt'),
        ];

        self::assertEquals($expected, array_map($formatter, iterator_to_array($absolute($fs), false)));
        self::assertEquals($expected, array_map($formatter, iterator_to_array($relative($fs), false)));
        self::assertEquals($expected, array_map($formatter, iterator_to_array($directory($fs), false)));
    }

    public function testInvokeNotFound(): void {
        $fs   = $this->getFileSystem(__DIR__);
        $path = 'path/to/directory';

        self::expectException(DependencyUnresolvable::class);
        self::expectExceptionMessage('Dependency not found.');

        iterator_to_array(
            (new FileIterator($path))($fs),
            false,
        );
    }
}
