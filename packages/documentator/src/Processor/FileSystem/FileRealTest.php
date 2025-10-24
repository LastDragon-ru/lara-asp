<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Caster;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\FileSystemAdapter;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

use function sprintf;

/**
 * @internal
 */
#[CoversClass(FileReal::class)]
final class FileRealTest extends TestCase {
    public function testConstruct(): void {
        $path    = (new FilePath(__FILE__))->getNormalizedPath();
        $adapter = Mockery::mock(FileSystemAdapter::class);
        $adapter
            ->shouldReceive('isFile')
            ->once()
            ->andReturn(true);

        $file = new FileReal($adapter, $path, Mockery::mock(Caster::class));

        self::assertSame('php', $file->getExtension());
        self::assertSame('FileRealTest.php', $file->getName());
    }

    public function testConstructNotFile(): void {
        $path    = (new FilePath(__DIR__))->getNormalizedPath();
        $adapter = Mockery::mock(FileSystemAdapter::class);
        $adapter
            ->shouldReceive('isFile')
            ->once()
            ->andReturn(false);

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(sprintf('The `%s` is not a file.', $path));

        new FileReal($adapter, $path, Mockery::mock(Caster::class));
    }
}
