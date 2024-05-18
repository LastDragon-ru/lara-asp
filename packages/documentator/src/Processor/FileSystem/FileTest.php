<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

use function basename;
use function file_get_contents;
use function file_put_contents;
use function sprintf;

/**
 * @internal
 */
#[CoversClass(File::class)]
final class FileTest extends TestCase {
    public function testConstruct(): void {
        $file = new File(__FILE__, false);

        self::assertEquals(__FILE__, $file->getPath());
        self::assertEquals('php', $file->getExtension());
        self::assertEquals('FileTest.php', $file->getName());
    }

    public function testConstructNotNormalized(): void {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Path must be normalized, `/../file.txt` given.');

        new File('/../file.txt', false);
    }

    public function testConstructNotAbsolute(): void {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Path must be absolute, `../file.txt` given.');

        new File('../file.txt', false);
    }

    public function testConstructNotFile(): void {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(sprintf('The `%s` is not a file.', __DIR__));

        new File(__DIR__, false);
    }

    public function testGetContent(): void {
        $temp = self::getTempFile(__FILE__)->getPathname();
        $file = new File($temp, false);

        self::assertEquals(__FILE__, $file->getContent());
        self::assertNotFalse(file_put_contents($temp, __DIR__));
        self::assertEquals(__DIR__, file_get_contents($temp));
        self::assertEquals(__FILE__, $file->getContent());
    }

    public function testSetContent(): void {
        $temp = self::getTempFile(__FILE__)->getPathname();
        $file = new File($temp, false);

        self::assertEquals(__FILE__, $file->getContent());
        self::assertNotFalse(file_put_contents($temp, __DIR__));
        self::assertSame($file, $file->setContent(__METHOD__));
        self::assertEquals(__DIR__, file_get_contents($temp));
        self::assertEquals(__METHOD__, $file->getContent());
    }

    public function testSave(): void {
        $temp = self::getTempFile(__FILE__)->getPathname();
        $file = new File($temp, true);

        self::assertTrue($file->save()); // because no changes

        self::assertSame($file, $file->setContent(__METHOD__));

        self::assertTrue($file->save());

        self::assertEquals(__METHOD__, file_get_contents($temp));
    }

    public function testSaveReadonly(): void {
        $temp = self::getTempFile(__FILE__)->getPathname();
        $file = new File($temp, false);

        self::assertTrue($file->save()); // because no changes

        self::assertSame($file, $file->setContent(__METHOD__));

        self::assertFalse($file->save());

        self::assertEquals(__FILE__, file_get_contents($temp));
    }

    public function testContext(): void {
        // Prepare
        $context = new stdClass();
        $path    = __FILE__;

        // Base
        $file = new File($path, false);
        $file = $file->setContext($this, $context);

        self::assertNull($file->getContext(new stdClass()));
        self::assertSame($context, $file->getContext($this));

        // Should not reset because the same
        $file->setContent($file->getContent());

        self::assertSame($context, $file->getContext($this));

        // Should reset after writing
        $file->setContent(__METHOD__);

        self::assertNull($file->getContext($this));
    }

    public function testGetRelativePath(): void {
        $internal  = new File(__FILE__, false);
        $directory = new Directory(__DIR__, true);

        self::assertEquals(basename(__FILE__), $internal->getRelativePath($directory));
    }
}