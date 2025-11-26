<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithProcessor;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnresolvable;
use LastDragon_ru\Path\FilePath;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(FileReference::class)]
final class FileReferenceTest extends TestCase {
    use WithProcessor;

    public function testGetPath(): void {
        $filesystem = $this->getFileSystem(__DIR__);
        $path       = (new FilePath(__FILE__))->normalized();

        self::assertSame('path/to/file', (string) (new FileReference('path/to/file'))->getPath($filesystem));
        self::assertSame((string) $path, (string) (new FileReference($path))->getPath($filesystem));
    }

    public function testInvoke(): void {
        $fs       = $this->getFileSystem(__DIR__);
        $path     = (new FilePath(__FILE__))->normalized();
        $file     = $fs->getFile($path);
        $absolute = new FileReference($path->path);
        $relative = new FileReference($path->name);
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
