<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Path;

use LastDragon_ru\LaraASP\Core\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(FilePath::class)]
final class FilePathTest extends TestCase {
    public function testGetFilePath(): void {
        $relative = (new FilePath('relative/path/to/file.a'))->getFilePath('file.b');
        $absolute = (new FilePath('/path/to/file.a'))->getFilePath('/file.b');

        self::assertEquals('relative/path/to/file.b', (string) $relative);

        self::assertEquals('/file.b', (string) $absolute);
    }

    public function testGetParentPath(): void {
        $relative = (new FilePath('relative/path/to/file.a'))->getParentPath();
        $absolute = (new FilePath('/path/to/file.a'))->getParentPath();

        self::assertEquals('relative/path/to', (string) $relative);
        self::assertEquals('/path/to', (string) $absolute);
    }

    public function testGetDirectoryPath(): void {
        $relative = (new FilePath('relative/path/to/file.a'))->getDirectoryPath('directory');
        $absolute = (new FilePath('/path/to/file.a'))->getDirectoryPath('/directory');
        $null     = (new FilePath('/path/to/file.a'))->getDirectoryPath();

        self::assertEquals('relative/path/to/directory', (string) $relative);

        self::assertEquals('/directory', (string) $absolute);

        self::assertEquals('/path/to', (string) $null);
    }

    public function testGetExtension(): void {
        self::assertEquals('txt', (new FilePath('relative/path/to/file.txt'))->getExtension());
        self::assertNull((new FilePath('relative/path/to/file'))->getExtension());
    }
}
