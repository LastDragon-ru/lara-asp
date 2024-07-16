<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Core\Utils\Path;
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
        $directory = new Directory(Path::normalize(__DIR__), false);

        self::assertEquals('path/to/directory', (string) (new DirectoryReference('path/to/directory')));
        self::assertEquals($directory->getPath(), (string) (new DirectoryReference($directory)));
    }

    public function testInvoke(): void {
        $fs        = new FileSystem();
        $root      = new Directory(Path::normalize(__DIR__), false);
        $file      = new File(Path::normalize(__FILE__), false);
        $another   = new Directory(Path::normalize(__DIR__), false);
        $absolute  = new DirectoryReference(__DIR__);
        $relative  = new DirectoryReference('.');
        $reference = new DirectoryReference($another);

        self::assertEquals($root, $absolute($fs, $root, $file));
        self::assertEquals($root, $relative($fs, $root, $file));
        self::assertSame($another, $reference($fs, $root, $file));
    }

    public function testInvokeNotFound(): void {
        $fs   = new FileSystem();
        $root = new Directory(Path::normalize(__DIR__), false);
        $file = new File(Path::normalize(__FILE__), false);
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
