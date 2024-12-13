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

use function sprintf;

/**
 * @internal
 */
#[CoversClass(DirectoryReference::class)]
final class DirectoryReferenceTest extends TestCase {
    public function testToString(): void {
        $path      = (new DirectoryPath(__DIR__))->getNormalizedPath();
        $directory = new Directory($path);

        self::assertEquals('path/to/directory', (string) (new DirectoryReference('path/to/directory')));
        self::assertEquals((string) $directory, (string) (new DirectoryReference($directory)));
        self::assertEquals((string) $path, (string) (new DirectoryReference($path)));
    }

    public function testInvoke(): void {
        $fs        = new FileSystem();
        $dir       = (new DirectoryPath(__DIR__))->getNormalizedPath();
        $root      = new Directory($dir);
        $file      = new File((new FilePath(__FILE__))->getNormalizedPath());
        $another   = new Directory($dir);
        $dirpath   = new DirectoryReference($dir);
        $absolute  = new DirectoryReference(__DIR__);
        $relative  = new DirectoryReference('.');
        $reference = new DirectoryReference($another);

        self::assertEquals($root, $absolute($fs, $root, $file));
        self::assertEquals($root, $relative($fs, $root, $file));
        self::assertEquals($root, $dirpath($fs, $root, $file));
        self::assertSame($another, $reference($fs, $root, $file));
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

        (new DirectoryReference($path))($fs, $root, $file);
    }
}
