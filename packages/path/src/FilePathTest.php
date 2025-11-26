<?php declare(strict_types = 1);

namespace LastDragon_ru\Path;

use LastDragon_ru\Path\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(FilePath::class)]
final class FilePathTest extends TestCase {
    public function testGetFilePath(): void {
        $relative = (new FilePath('relative/path/to/file.a'))->getFilePath('file.b');
        $absolute = (new FilePath('/path/to/file.a'))->getFilePath('/file.b');

        self::assertSame('relative/path/to/file.b', (string) $relative);

        self::assertSame('/file.b', (string) $absolute);
    }

    public function testGetParentPath(): void {
        $relative = (new FilePath('relative/path/to/file.a'))->getParentPath();
        $absolute = (new FilePath('/path/to/file.a'))->getParentPath();

        self::assertSame('relative/path/to', (string) $relative);
        self::assertSame('/path/to', (string) $absolute);
    }

    public function testGetDirectoryPath(): void {
        $relative = (new FilePath('relative/path/to/file.a'))->getDirectoryPath('directory');
        $absolute = (new FilePath('/path/to/file.a'))->getDirectoryPath('/directory');
        $null     = (new FilePath('/path/to/file.a'))->getDirectoryPath();

        self::assertSame('relative/path/to/directory/', (string) $relative);

        self::assertSame('/directory/', (string) $absolute);

        self::assertSame('/path/to/', (string) $null);
    }

    public function testGetExtension(): void {
        self::assertSame('txt', (new FilePath('relative/path/to/file.txt'))->getExtension());
        self::assertNull((new FilePath('relative/path/to/file'))->getExtension());
    }

    public function testGetNormalizedPath(): void {
        self::assertSame('/any/path', (string) (new FilePath('/any/path'))->getNormalizedPath());
        self::assertSame('any/path', (string) (new FilePath('any/path'))->getNormalizedPath());
        self::assertSame('any/path', (string) (new FilePath('./any/path'))->getNormalizedPath());
        self::assertSame('any/path', (string) (new FilePath('././any/path'))->getNormalizedPath());
        self::assertSame('../any/path', (string) (new FilePath('./../any/path'))->getNormalizedPath());
        self::assertSame('path', (string) (new FilePath('./any/../path'))->getNormalizedPath());
        self::assertSame('', (string) (new FilePath('./'))->getNormalizedPath());
        self::assertSame('', (string) (new FilePath('.'))->getNormalizedPath());
        self::assertSame('..', (string) (new FilePath('../'))->getNormalizedPath());
        self::assertSame('..', (string) (new FilePath('..'))->getNormalizedPath());
        self::assertSame('path', (string) (new FilePath('./any/../path/.'))->getNormalizedPath());
        self::assertSame('/', (string) (new FilePath('/..'))->getNormalizedPath());
        self::assertSame('../any/path', (string) (new FilePath('.\\..\\any\\path'))->getNormalizedPath());
        self::assertSame('any/path', (string) (new FilePath('any\\path'))->getNormalizedPath());
        self::assertSame('/any/path', (string) (new FilePath('/any/path/'))->getNormalizedPath());
        self::assertSame('any/path', (string) (new FilePath('any/path/'))->getNormalizedPath());
    }
}
