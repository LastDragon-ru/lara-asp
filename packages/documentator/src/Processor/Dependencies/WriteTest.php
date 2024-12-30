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
#[CoversClass(Write::class)]
final class WriteTest extends TestCase {
    use WithProcessor;

    public function testGetPath(): void {
        $fs   = $this->getFileSystem(__DIR__);
        $path = (new FilePath(__FILE__))->getNormalizedPath();
        $file = $fs->getFile($path);

        self::assertEquals('path/to/file', (string) (new Write('path/to/file', ''))->getPath());
        self::assertEquals((string) $path, (string) (new Write($path, ''))->getPath());
        self::assertEquals((string) $file, (string) (new Write($file, ''))->getPath());
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

        self::assertEquals($file, (new Write($path, $content))($fs));
    }
}
