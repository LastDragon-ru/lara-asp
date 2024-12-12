<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
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
    public function testToString(): void {
        $path      = (new DirectoryPath(__DIR__))->getNormalizedPath();
        $directory = new Directory($path);

        self::assertEquals('path/to/directory', (string) (new FileIterator('path/to/directory')));
        self::assertEquals((string) $directory, (string) (new FileIterator($directory)));
        self::assertEquals((string) $path, (string) (new FileIterator($path)));
    }

    public function testInvoke(): void {
        $fs        = new FileSystem();
        $path      = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $root      = new Directory((new DirectoryPath(__DIR__))->getNormalizedPath());
        $file      = new File((new FilePath(__FILE__))->getNormalizedPath());
        $pattern   = '*.txt';
        $absolute  = new FileIterator($path, $pattern);
        $relative  = new FileIterator(basename((string) $path), $pattern);
        $directory = new FileIterator(new Directory($path), $pattern);
        $formatter = static function (File $file) use ($path): string {
            return (string) $path->getRelativePath($file->getPath());
        };
        $expected  = [
            'a/a.txt',
            'a/a/aa.txt',
            'a/b/ab.txt',
            'b/a/ba.txt',
            'b/b.txt',
            'b/b/bb.txt',
            'c.txt',
        ];

        self::assertEquals($expected, array_map($formatter, iterator_to_array($absolute($fs, $root, $file))));
        self::assertEquals($expected, array_map($formatter, iterator_to_array($relative($fs, $root, $file))));
        self::assertEquals($expected, array_map($formatter, iterator_to_array($directory($fs, $root, $file))));
    }

    public function testInvokeNotFound(): void {
        $fs   = new FileSystem();
        $root = new Directory((new DirectoryPath(__DIR__))->getNormalizedPath());
        $file = new File((new FilePath(__FILE__))->getNormalizedPath());
        $path = 'path/to/directory';

        self::expectException(DependencyNotFound::class);
        self::expectExceptionMessage(
            sprintf(
                'Dependency `%s` of `%s` not found (root: `%s`).',
                $path,
                $file->getName(),
                $root->getPath(),
            ),
        );

        iterator_to_array(
            (new FileIterator($path))($fs, $root, $file),
        );
    }
}
