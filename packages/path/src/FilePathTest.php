<?php declare(strict_types = 1);

namespace LastDragon_ru\Path;

use LastDragon_ru\Path\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(FilePath::class)]
final class FilePathTest extends TestCase {
    public function testPropertyName(): void {
        self::assertSame('c', (new FilePath('/a/b/c'))->name);
        self::assertSame('c', (new FilePath('/a/b/c/'))->name); // todo(core/Path): Should be an error here?
    }

    public function testFile(): void {
        $relative = (new FilePath('relative/path/to/file.a'))->file('file.b');
        $absolute = (new FilePath('/path/to/file.a'))->file('/file.b');

        self::assertSame('relative/path/to/file.b', (string) $relative);

        self::assertSame('/file.b', (string) $absolute);
    }

    public function testParent(): void {
        $relative = (new FilePath('relative/path/to/file.a'))->parent();
        $absolute = (new FilePath('/path/to/file.a'))->parent();

        self::assertSame('relative/path/to/', (string) $relative);
        self::assertSame('/path/to/', (string) $absolute);
    }

    public function testDirectory(): void {
        $relative = (new FilePath('relative/path/to/file.a'))->directory('directory');
        $absolute = (new FilePath('/path/to/file.a'))->directory('/directory');
        $null     = (new FilePath('/path/to/file.a'))->directory();

        self::assertSame('relative/path/to/directory/', (string) $relative);

        self::assertSame('/directory/', (string) $absolute);

        self::assertSame('/path/to/', (string) $null);
    }

    public function testPropertyExtension(): void {
        self::assertSame('txt', (new FilePath('relative/path/to/file.txt'))->extension);
        self::assertNull((new FilePath('relative/path/to/file'))->extension);
    }

    public function testNormalized(): void {
        self::assertSame('/any/path', (string) (new FilePath('/any/path'))->normalized());
        self::assertSame('any/path', (string) (new FilePath('any/path'))->normalized());
        self::assertSame('any/path', (string) (new FilePath('./any/path'))->normalized());
        self::assertSame('any/path', (string) (new FilePath('././any/path'))->normalized());
        self::assertSame('../any/path', (string) (new FilePath('./../any/path'))->normalized());
        self::assertSame('path', (string) (new FilePath('./any/../path'))->normalized());
        self::assertSame('', (string) (new FilePath('./'))->normalized());
        self::assertSame('', (string) (new FilePath('.'))->normalized());
        self::assertSame('..', (string) (new FilePath('../'))->normalized());
        self::assertSame('..', (string) (new FilePath('..'))->normalized());
        self::assertSame('path', (string) (new FilePath('./any/../path/.'))->normalized());
        self::assertSame('/', (string) (new FilePath('/..'))->normalized());
        self::assertSame('../any/path', (string) (new FilePath('.\\..\\any\\path'))->normalized());
        self::assertSame('any/path', (string) (new FilePath('any\\path'))->normalized());
        self::assertSame('/any/path', (string) (new FilePath('/any/path/'))->normalized());
        self::assertSame('any/path', (string) (new FilePath('any/path/'))->normalized());
    }
}
