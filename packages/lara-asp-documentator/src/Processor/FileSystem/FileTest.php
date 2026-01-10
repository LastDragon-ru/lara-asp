<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithProcessor;
use LastDragon_ru\Path\FilePath;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(File::class)]
final class FileTest extends TestCase {
    use WithProcessor;

    public function testConstructNotNormalized(): void {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Path must be normalized, `/../path` given.');

        new class(
            Mockery::mock(FileSystem::class),
            new FilePath('/../path'),
        ) extends File {
            // empty
        };
    }

    public function testConstructNotAbsolute(): void {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Path must be absolute, `../path` given.');

        new class(
            Mockery::mock(FileSystem::class),
            (new FilePath('../path'))->normalized(),
        ) extends File {
            // empty
        };
    }

    public function testPropertyName(): void {
        $filesystem = Mockery::mock(FileSystem::class);
        $fileA      = new File($filesystem, new FilePath('/path/to/file.txt'));
        $fileB      = new File($filesystem, new FilePath('/path/to/file'));

        self::assertSame('file.txt', $fileA->name);
        self::assertSame('file', $fileB->name);
    }

    public function testPropertyExtension(): void {
        $filesystem = Mockery::mock(FileSystem::class);
        $fileA      = new File($filesystem, new FilePath('/path/to/file.txt'));
        $fileB      = new File($filesystem, new FilePath('/path/to/file'));

        self::assertSame('txt', $fileA->extension);
        self::assertNull($fileB->extension);
    }

    public function testPropertyContent(): void {
        $filesystem = Mockery::mock(FileSystem::class);
        $content    = 'content';
        $file       = new File($filesystem, new FilePath('/file.txt'));

        $filesystem
            ->shouldReceive('read')
            ->with($file)
            ->once()
            ->andReturn($content);

        self::assertSame($content, $file->content);
    }
}
