<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithProcessor;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Caster;
use LastDragon_ru\Path\FilePath;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

/**
 * @internal
 */
#[CoversClass(File::class)]
final class FileTest extends TestCase {
    use WithProcessor;

    public function testAs(): void {
        $filesystem = Mockery::mock(FileSystem::class);
        $caster     = Mockery::mock(Caster::class);
        $value      = new stdClass();
        $path       = (new FilePath(__FILE__))->normalized();
        $file       = new class($filesystem, $caster, $path) extends File {
            // empty
        };

        $caster
            ->shouldReceive('castTo')
            ->with($file, $value::class)
            ->once()
            ->andReturn($value);

        self::assertSame($value, $file->as($value::class));
    }

    public function testConstructNotNormalized(): void {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Path must be normalized, `/../path` given.');

        new class(
            Mockery::mock(FileSystem::class),
            Mockery::mock(Caster::class),
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
            Mockery::mock(Caster::class),
            (new FilePath('../path'))->normalized(),
        ) extends File {
            // empty
        };
    }

    public function testPropertyName(): void {
        $filesystem = Mockery::mock(FileSystem::class);
        $caster     = Mockery::mock(Caster::class);
        $fileA      = new File($filesystem, $caster, new FilePath('/path/to/file.txt'));
        $fileB      = new File($filesystem, $caster, new FilePath('/path/to/file'));

        self::assertSame('file.txt', $fileA->name);
        self::assertSame('file', $fileB->name);
    }

    public function testPropertyExtension(): void {
        $filesystem = Mockery::mock(FileSystem::class);
        $caster     = Mockery::mock(Caster::class);
        $fileA      = new File($filesystem, $caster, new FilePath('/path/to/file.txt'));
        $fileB      = new File($filesystem, $caster, new FilePath('/path/to/file'));

        self::assertSame('txt', $fileA->extension);
        self::assertNull($fileB->extension);
    }

    public function testPropertyContent(): void {
        $filesystem = Mockery::mock(FileSystem::class);
        $caster     = Mockery::mock(Caster::class);
        $content    = 'content';
        $file       = new File($filesystem, $caster, new FilePath('/file.txt'));

        $filesystem
            ->shouldReceive('read')
            ->with($file)
            ->once()
            ->andReturn($content);

        self::assertSame($content, $file->content);
    }
}
