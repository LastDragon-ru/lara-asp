<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithProcessor;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Optional::class)]
final class OptionalTest extends TestCase {
    use WithProcessor;

    public function testGetPath(): void {
        $filesystem = $this->getFileSystem(__DIR__);
        $dependency = new FileReference('path/to/file');
        $optional   = new Optional($dependency);

        self::assertEquals($dependency->getPath($filesystem), $optional->getPath($filesystem));
    }

    public function testInvoke(): void {
        $fs         = $this->getFileSystem(__DIR__);
        $file       = $fs->getFile(__FILE__);
        $dependency = new FileReference(__FILE__);
        $optional   = new Optional($dependency);

        self::assertEquals($file, $dependency($fs));
        self::assertEquals($file, $optional($fs));
    }

    public function testInvokeNotFound(): void {
        $dependency = new FileReference('path/to/file');
        $optional   = new Optional($dependency);
        $fs         = $this->getFileSystem(__DIR__);

        self::assertNull($optional($fs));
    }
}
