<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithProcessor;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Caster;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\FileSystem\Content;
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
        $caster = Mockery::mock(Caster::class);
        $value  = new stdClass();
        $path   = (new FilePath(__FILE__))->normalized();
        $file   = new class($path, $caster) extends File {
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

        new class(new FilePath('/../path'), Mockery::mock(Caster::class)) extends File {
            // empty
        };
    }

    public function testConstructNotAbsolute(): void {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Path must be absolute, `../path` given.');

        new class(
            (new FilePath('../path'))->normalized(),
            Mockery::mock(Caster::class),
        ) extends File {
            // empty
        };
    }

    public function testGetRelativePath(): void {
        $fs      = $this->getFileSystem(__DIR__);
        $file    = $fs->getFile(new FilePath(__FILE__));
        $path    = (new FilePath(self::getTestData()->path('a/a.txt')))->normalized();
        $another = new class(
            (new FilePath(__FILE__))->normalized(),
            Mockery::mock(Caster::class),
        ) extends File {
            // empty
        };

        self::assertSame('FileTest.php', (string) $another->getRelativePath($file));
        self::assertSame('FileTest/a/a.txt', (string) $another->getRelativePath($path));
    }

    public function testPropertyName(): void {
        $caster = Mockery::mock(Caster::class);
        $fileA  = new File(new FilePath('/path/to/file.txt'), $caster);
        $fileB  = new File(new FilePath('/path/to/file'), $caster);

        self::assertSame('file.txt', $fileA->name);
        self::assertSame('file', $fileB->name);
    }

    public function testPropertyExtension(): void {
        $caster = Mockery::mock(Caster::class);
        $fileA  = new File(new FilePath('/path/to/file.txt'), $caster);
        $fileB  = new File(new FilePath('/path/to/file'), $caster);

        self::assertSame('txt', $fileA->extension);
        self::assertNull($fileB->extension);
    }

    public function testPropertyContent(): void {
        $content = 'content';
        $file    = Mockery::mock(File::class);
        $file->makePartial();
        $file
            ->shouldReceive('as')
            ->with(Content::class)
            ->once()
            ->andReturn(new Content($content));

        self::assertSame($content, $file->content);
    }
}
