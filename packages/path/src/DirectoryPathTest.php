<?php declare(strict_types = 1);

namespace LastDragon_ru\Path;

use LastDragon_ru\Path\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(DirectoryPath::class)]
final class DirectoryPathTest extends TestCase {
    public function testPropertyName(): void {
        $path       = new DirectoryPath('path/./to/./directory');
        $normalized = $path->normalized();

        self::assertSame('directory', $path->name);
        self::assertSame('directory', $normalized->name);
        self::assertSame('path/to/directory/', (string) $normalized);
        self::assertSame('', (new DirectoryPath('./'))->name);
    }

    public function testFile(): void {
        $relative = (new DirectoryPath('relative/path/to/directory'))->file('file.b');
        $absolute = (new DirectoryPath('/path/to/directory'))->file('/file.b');

        self::assertSame('relative/path/to/directory/file.b', (string) $relative);

        self::assertSame('/file.b', (string) $absolute);
    }

    public function testDirectory(): void {
        $relative = (new DirectoryPath('relative/path/to'))->directory('directory');
        $absolute = (new DirectoryPath('/path/to'))->directory('/directory');
        $null     = (new DirectoryPath('/path/to'))->directory();

        self::assertSame('relative/path/to/directory/', (string) $relative);

        self::assertSame('/directory/', (string) $absolute);

        self::assertSame('/path/to/', (string) $null);
    }

    public function testParent(): void {
        $relative = new DirectoryPath('relative/path/to/file');
        $absolute = new DirectoryPath('/absolute/path/to/file');

        self::assertSame('relative/path/to/', (string) $relative->parent());
        self::assertSame('relative/path/', (string) $relative->parent()->parent());

        self::assertSame('/absolute/path/to/', (string) $absolute->parent());
        self::assertSame('/absolute/path/', (string) $absolute->parent()->parent());
    }

    public function testContains(): void {
        $path = new DirectoryPath('/path/to/directory');

        self::assertFalse($path->contains($path));
        self::assertTrue($path->contains(new FilePath('/path/to/directory/file.md')));
        self::assertTrue($path->contains(new FilePath('file.md')));
        self::assertFalse($path->contains(new FilePath('/path/to/directory/../file.md')));
        self::assertFalse($path->contains(new FilePath('/path/to/file.md')));
    }

    public function testNormalized(): void {
        self::assertSame('./', (string) (new DirectoryPath(''))->normalized());
        self::assertSame('./', (string) (new DirectoryPath('.'))->normalized());
        self::assertSame('../', (string) (new DirectoryPath('..'))->normalized());
        self::assertSame('/path/to/', (string) (new DirectoryPath('/path/to'))->normalized());
    }
}
