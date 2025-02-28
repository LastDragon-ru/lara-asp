<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Metadata;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

use function sprintf;

/**
 * @internal
 */
#[CoversClass(FileHook::class)]
final class FileHookTest extends TestCase {
    public function testConstruct(): void {
        $hook = Hook::Before;
        $path = (new FilePath(__DIR__))->getFilePath("@.{$hook->value}")->getNormalizedPath();
        $file = new FileHook(Mockery::mock(Metadata::class), $path);

        self::assertSame($hook->value, $file->getExtension());
    }

    public function testConstructNotFile(): void {
        $path = (new FilePath(__FILE__))->getNormalizedPath();

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(sprintf('The `%s` is not a hook.', $path));

        new FileHook(Mockery::mock(Metadata::class), $path);
    }
}
