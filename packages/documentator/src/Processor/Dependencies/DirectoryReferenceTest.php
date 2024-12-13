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
        $dir       = (new DirectoryPath(__DIR__))->getNormalizedPath();
        $fs        = new FileSystem(new Directory($dir));
        $file      = new File((new FilePath(__FILE__))->getNormalizedPath());
        $another   = new Directory($dir);
        $dirpath   = new DirectoryReference($dir);
        $absolute  = new DirectoryReference(__DIR__);
        $relative  = new DirectoryReference('.');
        $reference = new DirectoryReference($another);

        self::assertEquals($fs->input, $absolute($fs, $file));
        self::assertEquals($fs->input, $relative($fs, $file));
        self::assertEquals($fs->input, $dirpath($fs, $file));
        self::assertSame($another, $reference($fs, $file));
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

        (new DirectoryReference($path))($fs, $file);
    }
}
