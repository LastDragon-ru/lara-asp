<?php declare(strict_types = 1);

namespace LastDragon_ru\Path;

use LastDragon_ru\Path\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(DirectoryPath::class)]
final class DirectoryPathTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    public function testPropertyName(): void {
        $path       = new DirectoryPath('path/./to/./directory');
        $normalized = $path->normalized();

        self::assertSame('directory', $path->name);
        self::assertSame('directory', $normalized->name);
        self::assertSame('path/to/directory/', (string) $normalized);
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

    #[DataProvider('dataProviderNormalized')]
    public function testNormalized(string $expected, string $path): void {
        self::assertSame($expected, (string) (new DirectoryPath($path))->normalized());
    }

    public function testRelative(): void {
        $root = new DirectoryPath('/root/path/to/directory.path');

        self::assertSame(
            './',
            (string) $root->relative($root),
        );
        self::assertSame(
            '../file',
            (string) $root->relative(new FilePath('/root/path/to/file')),
        );
        self::assertSame(
            '../directory/',
            (string) $root->relative(new DirectoryPath('/root/path/to/directory')),
        );
        self::assertSame(
            '../path/to/file',
            (string) $root->relative(new FilePath('/root/path/to/path/to/file')),
        );
        self::assertSame(
            '../path/to/directory/',
            (string) $root->relative(new DirectoryPath('/root/path/to/path/to/directory')),
        );
        self::assertSame(
            '../../../../directory/',
            (string) $root->relative(new DirectoryPath('/directory')),
        );
        self::assertSame(
            '../../../../file',
            (string) $root->relative(new FilePath('/file')),
        );
        self::assertSame(
            '../../../../file',
            (string) $root->relative(new FilePath('/./file')),
        );
    }

    public function testConcatHome(): void {
        $target   = new DirectoryPath('~/home');
        $relative = (new DirectoryPath('relative/path'))->concat($target);
        $absolute = (new DirectoryPath('/absolute/path'))->concat($target);

        self::assertSame('relative/path/~/home/', (string) $relative);
        self::assertTrue($relative->relative);
        self::assertSame('/absolute/path/~/home/', (string) $absolute);
        self::assertTrue($absolute->absolute);
    }

    public function testConcatAbsolute(): void {
        $target   = new DirectoryPath('/to/absolute/./path');
        $relative = (new DirectoryPath('relative/path'))->concat($target);
        $absolute = (new DirectoryPath('/absolute/path'))->concat($target);

        self::assertSame('relative/path/to/absolute/path/', (string) $relative);
        self::assertTrue($relative->relative);
        self::assertSame('/absolute/path/to/absolute/path/', (string) $absolute);
        self::assertTrue($absolute->absolute);
    }

    public function testConcatRelative(): void {
        $target   = new DirectoryPath('to/../relative/./path');
        $relative = (new DirectoryPath('relative/path'))->concat($target);
        $absolute = (new DirectoryPath('/absolute/path'))->concat($target);

        self::assertSame('relative/path/relative/path/', (string) $relative);
        self::assertTrue($relative->relative);
        self::assertSame('/absolute/path/relative/path/', (string) $absolute);
        self::assertTrue($absolute->absolute);
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{string, string}>
     */
    public static function dataProviderNormalized(): array {
        return [
            'empty'                          => ['./', ''],
            'unix root'                      => ['/', '/'],
            'unix root (backslash)'          => ['/', '\\'],
            'unix root path'                 => ['/path/to/', '/path/to'],
            'unix root path (backslash)'     => ['/path/to/', '\\path/to'],
            'unix home'                      => ['~/', '~'],
            'unix home (slash)'              => ['~/', '~/'],
            'unix home (backslash)'          => ['~/', '~\\'],
            'win drive'                      => ['C:/', 'C:'],
            'win root'                       => ['D:/', 'D:\\'],
            'win root (slash)'               => ['D:/', 'D:/'],
            'win root path'                  => ['D:/path/to/', 'D:\\path\\to'],
            'win root path (slash)'          => ['D:/path/to/', 'D:/path/to'],
            'win malformed'                  => ['C:path/to/', 'C:path\\to'],
            'dot'                            => ['./', '.'],
            'dot (slash)'                    => ['./', './'],
            'dot (backslash)'                => ['./', '.\\'],
            'dot path (slash)'               => ['path/to/', './path/to'],
            'dot path (backslash)'           => ['path/to/', '.\\path\\to'],
            'dot dot'                        => ['../', '..'],
            'dot dot (slash)'                => ['../', '../'],
            'dot dot (backslash)'            => ['../', '..\\'],
            'dot dot path (slash)'           => ['../path/to/', '../path/to'],
            'dot dot path (backslash)'       => ['../path/to/', '..\\path\\to'],
            'relative'                       => ['path/to/', 'path/to'],
            'relative (backslash)'           => ['path/to/', 'path\\to'],
            'relative dot'                   => ['path/to/', 'path/././/.//to'],
            'relative dot (backslash)'       => ['path/to/', 'path\\.\\.\\\\.\\\\to'],
            'relative dot dot'               => ['../file/', 'path/.//to/../../../file'],
            'relative dot dot (backslash)'   => ['../file/', 'path\\.\\\\to\\..\\..\\..\\file'],
            'absolute dot'                   => ['/path/to/', '/path/././/.//to'],
            'absolute dot (backslash)'       => ['/path/to/', '/path\\.\\.\\\\.\\\\to'],
            'absolute dot dot'               => ['/to/', '/path/./../../../to'],
            'absolute dot dot(backslash)'    => ['/to/', '\\path\\.\\..\\..\\..\\to'],
            'absolute unix home'             => ['~/to/', '~/path/./../../../to'],
            'absolute unix home (backslash)' => ['~/to/', '~\\path\\.\\..\\..\\..\\to'],
            'starts with tilde'              => ['~path/to/', '~path/to'],
            'starts with tilde (backslash)'  => ['~path/to/', '~path\\to'],
        ];
    }
    //</editor-fold>
}
