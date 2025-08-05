<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithProcessor;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnresolvable;
use PHPUnit\Framework\Attributes\CoversClass;

use function basename;

/**
 * @internal
 */
#[CoversClass(FileReference::class)]
final class FileReferenceTest extends TestCase {
    use WithProcessor;

    public function testGetPath(): void {
        $filesystem = $this->getFileSystem(__DIR__);
        $path       = (new FilePath(__FILE__))->getNormalizedPath();

        self::assertSame('path/to/file', (string) (new FileReference('path/to/file'))->getPath($filesystem));
        self::assertSame((string) $path, (string) (new FileReference($path))->getPath($filesystem));
    }

    public function testInvoke(): void {
        $fs       = $this->getFileSystem(__DIR__);
        $path     = (new FilePath(__FILE__))->getNormalizedPath();
        $file     = $fs->getFile($path);
        $absolute = new FileReference(__FILE__);
        $relative = new FileReference(basename(__FILE__));
        $filepath = new FileReference($path);

        self::assertEquals($file, $absolute($fs));
        self::assertEquals($file, $relative($fs));
        self::assertEquals($file, $filepath($fs));
    }

    public function testInvokeNotFound(): void {
        $fs   = $this->getFileSystem(__DIR__);
        $path = 'path/to/file';

        self::expectException(DependencyUnresolvable::class);
        self::expectExceptionMessage('Dependency not found.');

        (new FileReference($path))($fs);
    }
}
