<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\WithProcessor;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(FileCreate::class)]
final class FileCreateTest extends TestCase {
    use WithProcessor;

    public function testGetPath(): void {
        $path = (new FilePath(__FILE__))->getNormalizedPath();

        self::assertEquals('path/to/file', (string) (new FileCreate('path/to/file', ''))->getPath());
        self::assertEquals((string) $path, (string) (new FileCreate($path, ''))->getPath());
    }

    public function testInvoke(): void {
        $content = __DIR__;
        $path    = (new FilePath(__FILE__))->getNormalizedPath();
        $file    = Mockery::mock(File::class);
        $fs      = Mockery::mock(FileSystem::class);
        $fs
            ->shouldReceive('write')
            ->with($path, $content)
            ->once()
            ->andReturn($file);

        self::assertEquals($file, (new FileCreate($path, $content))($fs));
    }
}
