<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithProcessor;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use PHPUnit\Framework\Attributes\CoversClass;

use function array_map;
use function basename;
use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(DirectoryIterator::class)]
final class DirectoryIteratorTest extends TestCase {
    use WithProcessor;

    public function testGetPath(): void {
        $filesystem = $this->getFileSystem(__DIR__);
        $directory  = $filesystem->getDirectory(new DirectoryPath(__DIR__));
        $path       = $directory->getPath();

        self::assertSame(
            'path/to/directory',
            (string) (new DirectoryIterator('path/to/directory'))->getPath($filesystem),
        );
        self::assertSame((string) $directory, (string) (new DirectoryIterator($directory))->getPath($filesystem));
        self::assertSame((string) $path, (string) (new DirectoryIterator($path))->getPath($filesystem));
    }

    public function testInvoke(): void {
        $fs        = $this->getFileSystem(__DIR__);
        $path      = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $absolute  = new DirectoryIterator($path);
        $relative  = new DirectoryIterator(basename((string) $path));
        $directory = new DirectoryIterator($fs->getDirectory($path));
        $formatter = static function (Directory|DirectoryPath $item): string {
            return (string) $item;
        };
        $expected  = array_map($formatter, [
            $fs->input->getDirectoryPath('DirectoryIteratorTest/a'),
            $fs->input->getDirectoryPath('DirectoryIteratorTest/a/a'),
            $fs->input->getDirectoryPath('DirectoryIteratorTest/a/b'),
            $fs->input->getDirectoryPath('DirectoryIteratorTest/b'),
            $fs->input->getDirectoryPath('DirectoryIteratorTest/b/a'),
            $fs->input->getDirectoryPath('DirectoryIteratorTest/b/b'),
        ]);

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
            (new DirectoryIterator($path))($fs),
            false,
        );
    }
}
