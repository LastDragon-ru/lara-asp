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

use function basename;
use function sprintf;

/**
 * @internal
 */
#[CoversClass(FileReference::class)]
final class FileReferenceTest extends TestCase {
    public function testToString(): void {
        $path = (new FilePath(__FILE__))->getNormalizedPath();
        $file = new File($path, false);

        self::assertEquals('path/to/file', (string) (new FileReference('path/to/file')));
        self::assertEquals((string) $file, (string) (new FileReference($file)));
        self::assertEquals((string) $path, (string) (new FileReference($path)));
    }

    public function testInvoke(): void {
        $fs        = new FileSystem();
        $root      = new Directory((new DirectoryPath(__DIR__))->getNormalizedPath(), false);
        $path      = (new FilePath(__FILE__))->getNormalizedPath();
        $file      = new File($path, false);
        $another   = new File($path, false);
        $absolute  = new FileReference(__FILE__);
        $relative  = new FileReference(basename(__FILE__));
        $filepath  = new FileReference($path);
        $reference = new FileReference($another);

        self::assertEquals($file, $absolute($fs, $root, $file));
        self::assertEquals($file, $relative($fs, $root, $file));
        self::assertEquals($file, $filepath($fs, $root, $file));
        self::assertSame($another, $reference($fs, $root, $file));
    }

    public function testInvokeNotFound(): void {
        $fs   = new FileSystem();
        $root = new Directory((new DirectoryPath(__DIR__))->getNormalizedPath(), false);
        $file = new File((new FilePath(__FILE__))->getNormalizedPath(), false);
        $path = 'path/to/file';

        self::expectException(DependencyNotFound::class);
        self::expectExceptionMessage(
            sprintf(
                'Dependency `%s` of `%s` not found (root: `%s`).',
                $path,
                $file->getName(),
                $root->getPath(),
            ),
        );

        (new FileReference($path))($fs, $root, $file);
    }
}
