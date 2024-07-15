<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Core\Utils\Path;
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
#[CoversClass(FilesIterator::class)]
final class FilesIteratorTest extends TestCase {
    public function testToString(): void {
        $directory = new Directory(Path::normalize(__DIR__), false);

        self::assertEquals('path/to/directory', (string) (new FilesIterator('path/to/directory')));
        self::assertEquals($directory->getPath(), (string) (new FilesIterator($directory)));
    }

    public function testInvoke(): void {
        $fs        = new FileSystem();
        $path      = Path::normalize(self::getTestData()->path(''));
        $root      = new Directory(Path::normalize(__DIR__), false);
        $file      = new File(Path::normalize(__FILE__), false);
        $pattern   = '*.txt';
        $absolute  = new FilesIterator($path, $pattern);
        $relative  = new FilesIterator(basename($path), $pattern);
        $directory = new FilesIterator(new Directory($path, false), $pattern);
        $formatter = static function (File $file) use ($path): string {
            return  Path::getRelativePath($path, $file->getPath());
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
        $root = new Directory(__DIR__, false);
        $file = new File(__FILE__, false);
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
            (new FilesIterator($path))($fs, $root, $file),
        );
    }
}
