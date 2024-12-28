<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\WithProcessor;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Optional::class)]
final class OptionalTest extends TestCase {
    use WithProcessor;

    public function testGetPath(): void {
        $dependency = new FileReference('path/to/file');
        $optional   = new Optional($dependency);

        self::assertEquals($dependency->getPath(), $optional->getPath());
    }

    public function testInvoke(): void {
        $dependency = new FileReference(__FILE__);
        $optional   = new Optional($dependency);
        $file       = new File((new FilePath(__FILE__))->getNormalizedPath());
        $fs         = $this->getFileSystem(__DIR__);

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
