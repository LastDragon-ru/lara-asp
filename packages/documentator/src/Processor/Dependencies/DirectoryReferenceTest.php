<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function sprintf;

/**
 * @internal
 */
#[CoversClass(DirectoryReference::class)]
final class DirectoryReferenceTest extends TestCase {
    public function testGetPath(): void {
        $path = (new DirectoryPath(__DIR__))->getNormalizedPath();

        self::assertEquals('path/to/directory', (string) (new DirectoryReference('path/to/directory'))->getPath());
        self::assertEquals((string) $path, (string) (new DirectoryReference($path))->getPath());
    }

    public function testInvoke(): void {
        $dir      = (new DirectoryPath(__DIR__))->getNormalizedPath();
        $fs       = new FileSystem($dir);
        $another  = new Directory($dir);
        $dirpath  = new DirectoryReference($dir);
        $absolute = new DirectoryReference(__DIR__);
        $relative = new DirectoryReference('.');

        self::assertEquals($another, $absolute($fs));
        self::assertEquals($another, $relative($fs));
        self::assertEquals($another, $dirpath($fs));
    }

    public function testInvokeNotFound(): void {
        $fs   = new FileSystem((new DirectoryPath(__DIR__))->getNormalizedPath());
        $path = 'path/to/directory';

        self::expectException(DependencyUnresolvable::class);
        self::expectExceptionMessage(
            sprintf(
                'Dependency `%s` not found.',
                $path,
            ),
        );

        (new DirectoryReference($path))($fs);
    }
}
