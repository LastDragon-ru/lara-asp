<?php declare(strict_types = 1);

namespace LastDragon_ru\Path;

use LastDragon_ru\Path\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(DirectoryPath::class)]
final class DirectoryPathTest extends TestCase {
    public function testGetName(): void {
        $path       = new DirectoryPath('path/./to/./directory');
        $normalized = $path->getNormalizedPath();

        self::assertSame('directory', $path->getName());
        self::assertSame('directory', $normalized->getName());
        self::assertSame('path/to/directory/', (string) $normalized);
    }

    public function testGetFilePath(): void {
        $relative = (new DirectoryPath('relative/path/to/directory'))->getFilePath('file.b');
        $absolute = (new DirectoryPath('/path/to/directory'))->getFilePath('/file.b');

        self::assertSame('relative/path/to/directory/file.b', (string) $relative);

        self::assertSame('/file.b', (string) $absolute);
    }

    public function testGetDirectoryPath(): void {
        $relative = (new DirectoryPath('relative/path/to'))->getDirectoryPath('directory');
        $absolute = (new DirectoryPath('/path/to'))->getDirectoryPath('/directory');
        $null     = (new DirectoryPath('/path/to'))->getDirectoryPath();

        self::assertSame('relative/path/to/directory/', (string) $relative);

        self::assertSame('/directory/', (string) $absolute);

        self::assertSame('/path/', (string) $null);
    }

    public function testGetParentPath(): void {
        $relative = new DirectoryPath('relative/path/to/file');
        $absolute = new DirectoryPath('/absolute/path/to/file');

        self::assertSame('relative/path/to/', (string) $relative->getParentPath());
        self::assertSame('relative/path/', (string) $relative->getParentPath()->getParentPath());

        self::assertSame('/absolute/path/to/', (string) $absolute->getParentPath());
        self::assertSame('/absolute/path/', (string) $absolute->getParentPath()->getParentPath());

        self::assertSame((string) $relative->getDirectoryPath(), (string) $relative->getParentPath());
        self::assertSame((string) $absolute->getDirectoryPath(), (string) $absolute->getParentPath());
    }

    public function testIsInside(): void {
        $path = new DirectoryPath('/path/to/directory');

        self::assertFalse($path->isInside($path));
        self::assertTrue($path->isInside(new FilePath('/path/to/directory/file.md')));
        self::assertTrue($path->isInside(new FilePath('file.md')));
        self::assertFalse($path->isInside(new FilePath('/path/to/directory/../file.md')));
        self::assertFalse($path->isInside(new FilePath('/path/to/file.md')));
    }

    public function testGetNormalizedPath(): void {
        self::assertSame('/any/path/', (string) (new DirectoryPath('/any/path'))->getNormalizedPath());
        self::assertSame('any/path/', (string) (new DirectoryPath('any/path'))->getNormalizedPath());
        self::assertSame('any/path/', (string) (new DirectoryPath('./any/path'))->getNormalizedPath());
        self::assertSame('any/path/', (string) (new DirectoryPath('././any/path'))->getNormalizedPath());
        self::assertSame('../any/path/', (string) (new DirectoryPath('./../any/path'))->getNormalizedPath());
        self::assertSame('path/', (string) (new DirectoryPath('./any/../path'))->getNormalizedPath());
        self::assertSame('./', (string) (new DirectoryPath('./'))->getNormalizedPath());
        self::assertSame('./', (string) (new DirectoryPath('.'))->getNormalizedPath());
        self::assertSame('../', (string) (new DirectoryPath('../'))->getNormalizedPath());
        self::assertSame('../', (string) (new DirectoryPath('..'))->getNormalizedPath());
        self::assertSame('path/', (string) (new DirectoryPath('./any/../path/.'))->getNormalizedPath());
        self::assertSame('/', (string) (new DirectoryPath('/..'))->getNormalizedPath());
        self::assertSame('../any/path/', (string) (new DirectoryPath('.\\..\\any\\path'))->getNormalizedPath());
        self::assertSame('any/path/', (string) (new DirectoryPath('any\\path'))->getNormalizedPath());
        self::assertSame('/any/path/', (string) (new DirectoryPath('/any/path/'))->getNormalizedPath());
        self::assertSame('any/path/', (string) (new DirectoryPath('any/path/'))->getNormalizedPath());
    }
}
