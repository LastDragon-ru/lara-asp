<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\FileSystemAdapter;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Metadata;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

/**
 * @internal
 */
#[CoversClass(File::class)]
final class FileTest extends TestCase {
    public function testAs(): void {
        $metadata = Mockery::mock(Metadata::class);
        $adapter  = Mockery::mock(FileSystemAdapter::class);
        $value    = new stdClass();
        $path     = (new FilePath(__FILE__))->getNormalizedPath();
        $file     = new class($adapter, $path, $metadata) extends File {
            // empty
        };

        $metadata
            ->shouldReceive('get')
            ->with($file, $value::class)
            ->once()
            ->andReturn($value);

        self::assertSame($value, $file->as($value::class));
    }
}
