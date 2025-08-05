<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithProcessor;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Hook;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Context::class)]
final class ContextTest extends TestCase {
    use WithProcessor;

    public function testInvoke(): void {
        $file       = Mockery::mock(File::class);
        $dependency = new Context();
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('getFile')
            ->with(Hook::Context)
            ->once()
            ->andReturn($file);

        self::assertSame($file, ($dependency)($filesystem));
    }

    public function testGetPath(): void {
        $file       = Mockery::mock(File::class);
        $dependency = new Context();
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('getFile')
            ->with(Hook::Context)
            ->once()
            ->andReturn($file);

        self::assertSame($file, $dependency->getPath($filesystem));
    }
}
