<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Caster;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Adapter;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

use function sprintf;

/**
 * @internal
 */
#[CoversClass(File::class)]
final class FileTest extends TestCase {
    public function testConstruct(): void {
        $path    = (new FilePath(__FILE__))->getNormalizedPath();
        $adapter = Mockery::mock(Adapter::class);
        $adapter
            ->shouldReceive('isFile')
            ->once()
            ->andReturn(true);

        $file = new File($adapter, $path, Mockery::mock(Caster::class));

        self::assertSame('php', $file->getExtension());
        self::assertSame('FileTest.php', $file->getName());
    }

    public function testConstructNotFile(): void {
        $path    = (new FilePath(__DIR__))->getNormalizedPath();
        $adapter = Mockery::mock(Adapter::class);
        $adapter
            ->shouldReceive('isFile')
            ->once()
            ->andReturn(false);

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(sprintf('The `%s` is not a file.', $path));

        new File($adapter, $path, Mockery::mock(Caster::class));
    }

    public function testAs(): void {
        $caster  = Mockery::mock(Caster::class);
        $adapter = Mockery::mock(Adapter::class);
        $adapter
            ->shouldReceive('isFile')
            ->once()
            ->andReturn(true);

        $value = new stdClass();
        $path  = (new FilePath(__FILE__))->getNormalizedPath();
        $file  = new class($adapter, $path, $caster) extends File {
            // empty
        };

        $caster
            ->shouldReceive('castTo')
            ->with($file, $value::class)
            ->once()
            ->andReturn($value);

        self::assertSame($value, $file->as($value::class));
    }
}
