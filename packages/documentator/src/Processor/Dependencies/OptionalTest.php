<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Optional::class)]
final class OptionalTest extends TestCase {
    public function testToString(): void {
        $dependency = new FileReference('path/to/file');
        $optional   = new Optional($dependency);

        self::assertEquals((string) $dependency, (string) $optional);
    }

    public function testInvoke(): void {
        $dependency = new FileReference(__FILE__);
        $optional   = new Optional($dependency);
        $file       = new File((new FilePath(__FILE__))->getNormalizedPath());
        $root       = new Directory((new DirectoryPath(__DIR__))->getNormalizedPath());
        $fs         = new FileSystem();

        self::assertEquals($file, $dependency($fs, $root, $file));
        self::assertEquals($file, $optional($fs, $root, $file));
    }

    public function testInvokeNotFound(): void {
        $dependency = new FileReference('path/to/file');
        $optional   = new Optional($dependency);
        $file       = new File((new FilePath(__FILE__))->getNormalizedPath());
        $root       = new Directory((new DirectoryPath(__DIR__))->getNormalizedPath());
        $fs         = new FileSystem();

        self::assertNull($optional($fs, $root, $file));
    }
}
