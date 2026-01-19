<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Utils;

use LastDragon_ru\Path\FilePath;
use LastDragon_ru\PhpUnit\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function file_get_contents;

/**
 * @internal
 */
#[CoversClass(TempFile::class)]
final class TempFileTest extends TestCase {
    public function testWithoutParams(): void {
        $file = new TempFile();
        $path = $file->path->path;

        self::assertFileExists($path);

        unset($file);

        self::assertFileDoesNotExist($path);
    }

    public function testWithFilePath(): void {
        $path = new FilePath(__FILE__);
        $file = new TempFile($path);

        self::assertFileEquals(__FILE__, $file->path->path);
    }

    public function testWithContent(): void {
        $content = 'content';
        $file    = new TempFile($content);

        self::assertSame($content, file_get_contents($file->path->path));
    }
}
