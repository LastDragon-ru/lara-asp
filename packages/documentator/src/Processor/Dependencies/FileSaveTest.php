<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithProcessor;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Caster;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\Path\FilePath;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

use function dirname;

/**
 * @internal
 */
#[CoversClass(FileSave::class)]
final class FileSaveTest extends TestCase {
    use WithProcessor;

    public function testGetPath(): void {
        $fs   = $this->getFileSystem(dirname(__DIR__), __DIR__);
        $path = (new FilePath(__FILE__))->normalized();
        $file = $fs->get($path);

        self::assertSame(
            (string) $fs->output->file('path/to/file'),
            (string) (new FileSave('path/to/file', ''))->getPath($fs),
        );
        self::assertSame(
            (string) $path,
            (string) (new FileSave($path, ''))->getPath($fs),
        );
        self::assertSame(
            (string) $path,
            (string) (new FileSave($file, ''))->getPath($fs),
        );
    }

    public function testInvoke(): void {
        $content = __DIR__;
        $path    = new FilePath('/file.txt');
        $fs      = Mockery::mock(FileSystem::class);
        $file    = Mockery::mock(File::class, [$fs, $path, Mockery::mock(Caster::class)]);

        $fs
            ->shouldReceive('write')
            ->with($path, $content)
            ->once()
            ->andReturn($file);

        self::assertSame($file, (new FileSave($file, $content))($fs));
    }
}
