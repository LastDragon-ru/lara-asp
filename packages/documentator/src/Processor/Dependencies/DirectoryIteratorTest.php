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
#[CoversClass(DirectoryIterator::class)]
final class DirectoryIteratorTest extends TestCase {
    public function testToString(): void {
        $path      = (new DirectoryPath(__DIR__))->getNormalizedPath();
        $directory = new Directory($path);

        self::assertEquals('path/to/directory', (string) (new DirectoryIterator('path/to/directory')));
        self::assertEquals((string) $directory, (string) (new DirectoryIterator($directory)));
        self::assertEquals((string) $path, (string) (new DirectoryIterator($path)));
    }

    public function testInvoke(): void {
        $fs        = new FileSystem(new Directory((new DirectoryPath(__DIR__))->getNormalizedPath()));
        $path      = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $file      = new File((new FilePath(__FILE__))->getNormalizedPath());
        $absolute  = new DirectoryIterator($path);
        $relative  = new DirectoryIterator(basename((string) $path));
        $directory = new DirectoryIterator(new Directory($path));
        $formatter = static function (Directory $directory) use ($path): string {
            return (string) $path->getRelativePath($directory->getPath());
        };
        $expected  = [
            'a',
            'a/a',
            'a/b',
            'b',
            'b/a',
            'b/b',
        ];

        self::assertEquals($expected, array_map($formatter, iterator_to_array($absolute($fs, $file))));
        self::assertEquals($expected, array_map($formatter, iterator_to_array($relative($fs, $file))));
        self::assertEquals($expected, array_map($formatter, iterator_to_array($directory($fs, $file))));
    }

    public function testInvokeNotFound(): void {
        $fs   = new FileSystem(new Directory((new DirectoryPath(__DIR__))->getNormalizedPath()));
        $file = new File((new FilePath(__FILE__))->getNormalizedPath());
        $path = 'path/to/directory';

        self::expectException(DependencyNotFound::class);
        self::expectExceptionMessage(
            sprintf(
                'Dependency `%s` of `%s` not found (root: `%s`).',
                $path,
                $file->getName(),
                $fs->input->getPath(),
            ),
        );

        iterator_to_array(
            (new DirectoryIterator($path))($fs, $file),
        );
    }
}
