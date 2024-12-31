<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Path;

use LastDragon_ru\LaraASP\Core\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(DirectoryPath::class)]
final class DirectoryPathTest extends TestCase {
    public function testGetFilePath(): void {
        $relative = (new DirectoryPath('relative/path/to/directory'))->getFilePath('file.b');
        $absolute = (new DirectoryPath('/path/to/directory'))->getFilePath('/file.b');

        self::assertEquals('relative/path/to/directory/file.b', (string) $relative);

        self::assertEquals('/file.b', (string) $absolute);
    }

    public function testGetDirectoryPath(): void {
        $relative = (new DirectoryPath('relative/path/to'))->getDirectoryPath('directory');
        $absolute = (new DirectoryPath('/path/to'))->getDirectoryPath('/directory');
        $null     = (new DirectoryPath('/path/to'))->getDirectoryPath();

        self::assertEquals('relative/path/to/directory', (string) $relative);

        self::assertEquals('/directory', (string) $absolute);

        self::assertEquals('/path', (string) $null);
    }

    public function testGetParentPath(): void {
        $relative = new DirectoryPath('relative/path/to/file');
        $absolute = new DirectoryPath('/absolute/path/to/file');

        self::assertEquals('relative/path/to', (string) $relative->getParentPath());
        self::assertEquals('relative/path', (string) $relative->getParentPath()->getParentPath());

        self::assertEquals('/absolute/path/to', (string) $absolute->getParentPath());
        self::assertEquals('/absolute/path', (string) $absolute->getParentPath()->getParentPath());

        self::assertEquals((string) $relative->getDirectoryPath(), (string) $relative->getParentPath());
        self::assertEquals((string) $absolute->getDirectoryPath(), (string) $absolute->getParentPath());
    }

    public function testIsInside(): void {
        $path = new DirectoryPath('/path/to/directory');

        self::assertFalse($path->isInside($path));
        self::assertTrue($path->isInside(new FilePath('/path/to/directory/file.md')));
        self::assertTrue($path->isInside(new FilePath('file.md')));
        self::assertFalse($path->isInside(new FilePath('/path/to/directory/../file.md')));
        self::assertFalse($path->isInside(new FilePath('/path/to/file.md')));
    }
}
