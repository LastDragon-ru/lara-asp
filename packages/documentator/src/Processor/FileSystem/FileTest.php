<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

use function file_get_contents;
use function file_put_contents;
use function sprintf;

/**
 * @internal
 */
#[CoversClass(File::class)]
final class FileTest extends TestCase {
    public function testConstruct(): void {
        $path = (new FilePath(__FILE__))->getNormalizedPath();
        $file = new File(Mockery::mock(MetadataResolver::class), $path);

        self::assertEquals('php', $file->getExtension());
        self::assertEquals('FileTest.php', $file->getName());
    }

    public function testConstructNotFile(): void {
        $path = (new FilePath(__DIR__))->getNormalizedPath();

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(sprintf('The `%s` is not a file.', $path));

        new File(Mockery::mock(MetadataResolver::class), $path);
    }

    public function testGetContent(): void {
        $temp = (new FilePath(self::getTempFile(__FILE__)->getPathname()))->getNormalizedPath();
        $file = new File(Mockery::mock(MetadataResolver::class), $temp);
        $path = (string) $file;

        self::assertEquals(__FILE__, $file->getContent());
        self::assertNotFalse(file_put_contents($path, __DIR__));
        self::assertEquals(__DIR__, file_get_contents($path));
        self::assertEquals(__FILE__, $file->getContent());
    }

    public function testSetContent(): void {
        $temp = (new FilePath(self::getTempFile(__FILE__)->getPathname()))->getNormalizedPath();
        $file = new File(Mockery::mock(MetadataResolver::class), $temp);
        $path = (string) $file;

        self::assertEquals(__FILE__, $file->getContent());
        self::assertNotFalse(file_put_contents($path, __DIR__));
        self::assertSame($file, $file->setContent(__METHOD__));
        self::assertEquals(__DIR__, file_get_contents($path));
        self::assertEquals(__METHOD__, $file->getContent());
    }
}
