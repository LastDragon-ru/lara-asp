<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts\Php;

use LastDragon_ru\LaraASP\Documentator\Composer\ComposerJson;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithProcessor;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\Path\FilePath;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Composer::class)]
final class ComposerTest extends TestCase {
    use WithProcessor;

    public function testInvoke(): void {
        $filesystem = Mockery::mock(FileSystem::class);
        $resolver   = $this->getProcessorResolver($filesystem);
        $content    = '{"name": "test"}';
        $cast       = new Composer();
        $path       = new FilePath('/path/to/file.json');
        $file       = Mockery::mock(File::class, [$filesystem, $path]);
        $filesystem
            ->shouldReceive('read')
            ->with($file)
            ->once()
            ->andReturn($content);

        self::assertEquals(new ComposerJson('test'), $cast($resolver, $file)->json);
    }
}
