<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Metadata;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

use function sprintf;

/**
 * @internal
 */
#[CoversClass(File::class)]
final class FileTest extends TestCase {
    public function testConstruct(): void {
        $path = (new FilePath(__FILE__))->getNormalizedPath();
        $file = new File(Mockery::mock(MetadataResolver::class), $path);

        self::assertSame('php', $file->getExtension());
        self::assertSame('FileTest.php', $file->getName());
    }

    public function testConstructNotFile(): void {
        $path = (new FilePath(__DIR__))->getNormalizedPath();

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(sprintf('The `%s` is not a file.', $path));

        new File(Mockery::mock(MetadataResolver::class), $path);
    }

    public function testGetMetadata(): void {
        $metadata = Mockery::mock(MetadataResolver::class);
        $path     = (new FilePath(__FILE__))->getNormalizedPath();
        $file     = new File($metadata, $path);

        $metadata
            ->shouldReceive('get')
            ->with($file, Metadata::class)
            ->once()
            ->andReturn(123);

        self::assertSame(123, $file->getMetadata(Metadata::class));
    }
}
