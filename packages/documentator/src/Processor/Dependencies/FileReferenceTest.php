<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnresolvable;
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

        self::assertEquals('path/to/file', (string) (new FileReference('path/to/file')));
        self::assertEquals((string) $path, (string) (new FileReference($path)));
    }

    public function testInvoke(): void {
        $fs       = new FileSystem((new DirectoryPath(__DIR__))->getNormalizedPath());
        $path     = (new FilePath(__FILE__))->getNormalizedPath();
        $file     = new File($path);
        $absolute = new FileReference(__FILE__);
        $relative = new FileReference(basename(__FILE__));
        $filepath = new FileReference($path);

        self::assertEquals($file, $absolute($fs));
        self::assertEquals($file, $relative($fs));
        self::assertEquals($file, $filepath($fs));
    }

    public function testInvokeNotFound(): void {
        $fs   = new FileSystem((new DirectoryPath(__DIR__))->getNormalizedPath());
        $path = 'path/to/file';

        self::expectException(DependencyUnresolvable::class);
        self::expectExceptionMessage(
            sprintf(
                'Dependency `%s` not found.',
                $path,
            ),
        );

        (new FileReference($path))($fs);
    }
}
