<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
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
        self::assertEquals('path/to/file', (string) (new FileReference('path/to/file')));
    }

    public function testInvoke(): void {
        $root     = new Directory(Path::normalize(__DIR__), false);
        $file     = new File(Path::normalize(__FILE__), false);
        $absolute = new FileReference(__FILE__);
        $relative = new FileReference(basename(__FILE__));

        self::assertEquals($file, $absolute($root, $file));
        self::assertEquals($file, $relative($root, $file));
    }

    public function testInvokeNotFound(): void {
        $root = new Directory(__DIR__, false);
        $file = new File(__FILE__, false);
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

        (new FileReference($path))($root, $file);
    }
}
