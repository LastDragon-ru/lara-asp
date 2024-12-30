<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\WithProcessor;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(FileSave::class)]
final class FileSaveTest extends TestCase {
    use WithProcessor;

    public function testGetPath(): void {
        $fs   = $this->getFileSystem(__DIR__);
        $file = $fs->getFile(__FILE__);

        self::assertEquals((string) $file, (string) (new FileSave($file, ''))->getPath());
    }

    public function testInvoke(): void {
        $content = __DIR__;
        $file    = Mockery::mock(File::class);
        $fs      = Mockery::mock(FileSystem::class);
        $fs
            ->shouldReceive('write')
            ->with($file, $content)
            ->once()
            ->andReturn($file);

        self::assertEquals(null, (new FileSave($file, $content))($fs));
    }
}
