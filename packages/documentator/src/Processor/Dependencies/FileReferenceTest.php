<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Core\Utils\Path;
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
        $file = new File(Path::normalize(__FILE__), false);

        self::assertEquals('path/to/file', (string) (new FileReference('path/to/file')));
        self::assertEquals($file->getPath(), (string) (new FileReference($file)));
    }

    public function testInvoke(): void {
        $fs        = new FileSystem();
        $root      = new Directory(Path::normalize(__DIR__), false);
        $file      = new File(Path::normalize(__FILE__), false);
        $another   = new File(Path::normalize(__FILE__), false);
        $absolute  = new FileReference(__FILE__);
        $relative  = new FileReference(basename(__FILE__));
        $reference = new FileReference($another);

        self::assertEquals($file, $absolute($fs, $root, $file));
        self::assertEquals($file, $relative($fs, $root, $file));
        self::assertSame($another, $reference($fs, $root, $file));
    }

    public function testInvokeNotFound(): void {
        $fs   = new FileSystem();
        $root = new Directory(Path::normalize(__DIR__), false);
        $file = new File(Path::normalize(__FILE__), false);
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
