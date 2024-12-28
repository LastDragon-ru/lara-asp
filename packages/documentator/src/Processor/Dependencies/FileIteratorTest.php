<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\WithProcessor;
use PHPUnit\Framework\Attributes\CoversClass;

use function array_map;
use function basename;
use function iterator_to_array;
use function sprintf;

/**
 * @internal
 */
#[CoversClass(FileIterator::class)]
final class FileIteratorTest extends TestCase {
    use WithProcessor;

    public function testGetPath(): void {
        $path      = (new DirectoryPath(__DIR__))->getNormalizedPath();
        $directory = new Directory($path);

        self::assertEquals('path/to/directory', (string) (new FileIterator('path/to/directory'))->getPath());
        self::assertEquals((string) $directory, (string) (new FileIterator($directory))->getPath());
        self::assertEquals((string) $path, (string) (new FileIterator($path))->getPath());
    }

    public function testInvoke(): void {
        $fs        = $this->getFileSystem(__DIR__);
        $path      = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $pattern   = '*.txt';
        $absolute  = new FileIterator($path, $pattern);
        $relative  = new FileIterator(basename((string) $path), $pattern);
        $directory = new FileIterator(new Directory($path), $pattern);
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

        self::assertEquals($expected, array_map($formatter, iterator_to_array($absolute($fs))));
        self::assertEquals($expected, array_map($formatter, iterator_to_array($relative($fs))));
        self::assertEquals($expected, array_map($formatter, iterator_to_array($directory($fs))));
    }

    public function testInvokeNotFound(): void {
        $fs   = $this->getFileSystem(__DIR__);
        $path = 'path/to/directory';

        self::expectException(DependencyUnresolvable::class);
        self::expectExceptionMessage(
            sprintf(
                'Dependency `%s` not found.',
                $path,
            ),
        );

        iterator_to_array(
            (new FileIterator($path))($fs),
        );
    }
}
