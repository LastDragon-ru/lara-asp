<?php declare(strict_types = 1);

namespace LastDragon_ru\Path;

use LastDragon_ru\Path\Package\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(Path::class)]
final class PathTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    public function testToString(): void {
        $path   = '/a/b/c';
        $object = new PathTest_Path($path);

        self::assertSame($path, (string) $object);
    }

    public function testPropertyName(): void {
        self::assertSame('c', (new PathTest_Path('/a/b/c'))->name);
        self::assertSame('c', (new PathTest_Path('/a/b/c/'))->name);
    }

    public function testPropertyRelative(): void {
        self::assertFalse((new PathTest_Path('/'))->relative);
        self::assertFalse((new PathTest_Path('C:'))->relative);
        self::assertTrue((new PathTest_Path('./'))->relative);
        self::assertTrue((new PathTest_Path('C:path'))->relative);
    }

    public function testPropertyNormalized(): void {
        self::assertTrue((new PathTest_Path('/any/path'))->normalized);
        self::assertTrue((new PathTest_Path('any/path'))->normalized);
        self::assertFalse((new PathTest_Path('any/path/'))->normalized);
        self::assertFalse((new PathTest_Path('./any//path'))->normalized);
        self::assertFalse((new PathTest_Path('././any/path'))->normalized);
        self::assertFalse((new PathTest_Path('./../any/path'))->normalized);
    }

    /**
     * @param array{Type, string} $expected
     */
    #[DataProvider('dataProviderResolve')]
    public function testResolve(array $expected, string $root, string $path): void {
        $actual = Path::make($root)->resolve(Path::make($path));

        self::assertSame($expected, [$actual->type, $actual->path]);
    }

    public function testNormalized(): void {
        $path     = 'path/to';
        $instance = Mockery::mock(Path::class, [$path]);
        $instance->shouldAllowMockingProtectedMethods();
        $instance->makePartial();
        $instance
            ->shouldReceive('normalize')
            ->with(Type::Relative, ['', 'path', 'to'])
            ->twice()
            ->andReturn('normalized');

        $normalized = $instance->normalized();

        self::assertSame('normalized', (string) $normalized);
        self::assertNotSame($normalized, $instance->normalized());
    }

    #[DataProvider('dataProviderRelative')]
    public function testRelative(?string $expected, string $root, string $path): void {
        $root   = Path::make($root);
        $actual = $root->relative(Path::make($path));

        self::assertSame($expected, $actual?->path);
    }

    #[DataProvider('dataProviderType')]
    public function testPropertyType(Type $expected, string $path): void {
        self::assertSame($expected, (new PathTest_Path($path))->type);
    }

    /**
     * @param list<string> $expected
     */
    #[DataProvider('dataProviderParts')]
    public function testPropertyParts(array $expected, string $path): void {
        self::assertSame($expected, (new PathTest_Path($path))->parts);
    }

    public function testFile(): void {
        $relative = (new PathTest_Path('relative/path/to'))->file('file');
        $absolute = (new PathTest_Path('/path/to'))->file('/file');

        self::assertSame('relative/path/file', (string) $relative);

        self::assertSame('/file', (string) $absolute);
    }

    public function testDirectory(): void {
        $relative = (new PathTest_Path('./relative/./path/to'))->directory('directory');
        $absolute = (new PathTest_Path('/./path/./to'))->directory('/directory');
        $null     = (new PathTest_Path('/./path/./to'))->directory();

        self::assertSame('relative/path/directory/', (string) $relative);
        self::assertSame('/directory/', (string) $absolute);
        self::assertSame('/path/', (string) $null);
    }

    public function testEquals(): void {
        self::assertTrue((new PathTest_Path('path/to/file'))->equals(new PathTest_Path('path/to/file')));
        self::assertTrue((new PathTest_Path('path/to/file'))->equals(new PathTest_Path('path/./to/./file')));
        self::assertTrue(
            (new PathTest_Path('path/to/file'))->equals(
                new class('path/to/file') extends PathTest_Path {
                    // empty
                },
            ),
        );
        self::assertFalse((new PathTest_Path('path/to/file'))->equals(new PathTest_Path('path/to')));
        self::assertFalse((new PathTest_Path('path/to/file'))->equals(null));
        self::assertFalse(
            (new PathTest_Path('path/to/file'))->equals(
            // @phpstan-ignore class.disallowedSubtype (for test)
                new class('path/to/file') extends Path {
                    // path must be a subclass of
                },
            ),
        );
    }

    /**
     * @param class-string $expected
     */
    #[DataProvider('dataProviderMake')]
    public function testMake(string $expected, string $path): void {
        self::assertInstanceOf($expected, Path::make($path));
    }

    #[DataProvider('dataProviderNormalize')]
    public function testNormalize(string $expected, string $path): void {
        self::assertSame($expected, Path::make($path)->normalized()->path);
    }

    public function testIs(): void {
        $path = new PathTest_Path('path');

        self::assertTrue($path->is(Type::Home, Type::Relative));
        self::assertTrue($path->is(Type::Relative));
        self::assertFalse($path->is(Type::Absolute));
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<string, array{class-string<DirectoryPath|FilePath>, string}>
     */
    public static function dataProviderMake(): array {
        return [
            ''       => [DirectoryPath::class, ''],
            '~'      => [DirectoryPath::class, '~'],
            '~/'     => [DirectoryPath::class, '~/'],
            '~user'  => [DirectoryPath::class, '~user'],
            '~user/' => [DirectoryPath::class, '~user/'],
            '.'      => [DirectoryPath::class, '.'],
            './'     => [DirectoryPath::class, './'],
            '.\\'    => [DirectoryPath::class, '.\\'],
            '..'     => [DirectoryPath::class, '..'],
            '../'    => [DirectoryPath::class, '../'],
            '..\\'   => [DirectoryPath::class, '..\\'],
            'path/'  => [DirectoryPath::class, 'path/'],
            '/path/' => [DirectoryPath::class, '/path/'],
            'path'   => [FilePath::class, 'path'],
            '/path'  => [FilePath::class, '/path'],
            './path' => [FilePath::class, './path'],
            '/~'     => [FilePath::class, '/~'],
            './~'    => [FilePath::class, './~'],
        ];
    }

    /**
     * @return array<string, array{Type, string}>
     */
    public static function dataProviderType(): array {
        return [
            'empty'                        => [Type::Relative, ''],
            '///'                          => [Type::Absolute, '///'],
            '\\\\\\'                       => [Type::Absolute, '\\\\\\'],
            'root'                         => [Type::Absolute, '/'],
            'root (backslash)'             => [Type::Absolute, '\\'],
            'root path'                    => [Type::Absolute, '/path/to'],
            'root path (backslash)'        => [Type::Absolute, '\\path/to'],
            'home'                         => [Type::Home, '~'],
            'home (slash)'                 => [Type::Home, '~/'],
            'home (backslash)'             => [Type::Home, '~\\'],
            'home (path)'                  => [Type::Home, '~/path\\to'],
            'home (user)'                  => [Type::User, '~user'],
            'home (user slash)'            => [Type::User, '~user/path'],
            'home (user backslash)'        => [Type::User, '~user\\path'],
            'home (tilde)'                 => [Type::Relative, './~'],
            'home (tilde path)'            => [Type::Relative, './~path'],
            'windows (disk)'               => [Type::WindowsAbsolute, 'C:'],
            'windows'                      => [Type::WindowsAbsolute, 'D:\\'],
            'windows (slash)'              => [Type::WindowsAbsolute, 'D:/'],
            'windows path'                 => [Type::WindowsAbsolute, 'D:\\path\\to'],
            'windows path (slash)'         => [Type::WindowsAbsolute, 'D:/path/to'],
            'windows path (lowercase)'     => [Type::WindowsAbsolute, 'd:/path/to'],
            'windows relative'             => [Type::WindowsRelative, 'C:path\\to'],
            'windows relative (slash)'     => [Type::WindowsRelative, 'C:path/to'],
            'windows relative (lowercase)' => [Type::WindowsRelative, 'c:path/to'],
            'windows (malformed)'          => [Type::Relative, '0:'],
            'windows path (malformed)'     => [Type::Relative, '0:\\path'],
            'url (https)'                  => [Type::Relative, 'https://example.com'],
            'url (mailto)'                 => [Type::Relative, 'mailto:example@example.com'],
            'dot'                          => [Type::Relative, '.'],
            'dot (slash)'                  => [Type::Relative, './'],
            'dot (backslash)'              => [Type::Relative, '.\\'],
            'dot path (slash)'             => [Type::Relative, './path/to'],
            'dot path (backslash)'         => [Type::Relative, '.\\path\\to'],
            'dot dot'                      => [Type::Relative, '..'],
            'dot dot (slash)'              => [Type::Relative, '../'],
            'dot dot (backslash)'          => [Type::Relative, '..\\'],
            'dot dot path (slash)'         => [Type::Relative, '../path/to'],
            'dot dot path (backslash)'     => [Type::Relative, '..\\path\\to'],
            'relative'                     => [Type::Relative, 'path/to'],
            'relative (backslash)'         => [Type::Relative, 'path\\to'],
            'unc'                          => [Type::Unc, '//server/share/path/to/file.txt'],
            'unc (backslash)'              => [Type::Unc, '\\\\server\\share\\path\\to\\file.txt'],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function dataProviderNormalize(): array {
        return [
            'empty'                        => ['./', ''],
            '///'                          => ['/', '///'],
            '\\\\\\'                       => ['/', '\\\\\\'],
            'root'                         => ['/', '/'],
            'root (backslash)'             => ['/', '\\'],
            'root path'                    => ['/path/to', '/path/to'],
            'root path (backslash)'        => ['/path/to', '\\path/to'],
            'home'                         => ['~/', '~'],
            'home (slash)'                 => ['~/', '~/'],
            'home (backslash)'             => ['~/', '~\\'],
            'home (path)'                  => ['~/path/to', '~/path\\to'],
            'home (user)'                  => ['~user/', '~user'],
            'home (user slash)'            => ['~user/path', '~user/path'],
            'home (user backslash)'        => ['~user/path', '~user\\path'],
            'home (tilde)'                 => ['./~', './~'],
            'home (tilde path)'            => ['./~path', './~path'],
            'windows (disk)'               => ['C:/', 'C:'],
            'windows'                      => ['D:/', 'D:\\'],
            'windows (slash)'              => ['D:/', 'D:/'],
            'windows path (lowercase)'     => ['D:/path/to', 'd:\\path\\to'],
            'windows path'                 => ['D:/path/to', 'D:\\path\\to'],
            'windows path (slash)'         => ['D:/path/to', 'D:/path/to'],
            'windows relative'             => ['C:path/to', 'C:path\\to'],
            'windows relative (slash)'     => ['C:path/to', 'C:path/to'],
            'windows relative (lowercase)' => ['C:path/to', 'c:path\\to'],
            'windows (malformed)'          => ['0:', '0:'],
            'windows path (malformed)'     => ['0:/path', '0:\\path'],
            'dot'                          => ['./', '.'],
            'dot (slash)'                  => ['./', './'],
            'dot (backslash)'              => ['./', '.\\'],
            'dot path (slash)'             => ['path/to', './path/to'],
            'dot path (backslash)'         => ['path/to', '.\\path\\to'],
            'dot dot'                      => ['../', '..'],
            'dot dot (slash)'              => ['../', '../'],
            'dot dot (backslash)'          => ['../', '..\\'],
            'dot dot path (slash)'         => ['../path/to', '../path/to'],
            'dot dot path (backslash)'     => ['../path/to', '..\\path\\to'],
            'relative'                     => ['path/to', 'path/to'],
            'relative (backslash)'         => ['path/to', 'path\\to'],
            'relative dot'                 => ['path/to', 'path/././/.//to'],
            'relative dot (backslash)'     => ['path/to', 'path\\.\\.\\\\.\\\\to'],
            'relative dot dot'             => ['../file', 'path/.//to/../../../file'],
            'relative dot dot (backslash)' => ['../file', 'path\\.\\\\to\\..\\..\\..\\file'],
            'absolute dot'                 => ['/path/to', '/path/././/.//to'],
            'absolute dot (backslash)'     => ['/path/to', '/path\\.\\.\\\\.\\\\to'],
            'absolute dot dot'             => ['/to', '/path/./../../../to'],
            'absolute dot dot(backslash)'  => ['/to', '\\path\\.\\..\\..\\..\\to'],
            'absolute home'                => ['~/to', '~/path/./../../../to'],
            'absolute home (backslash)'    => ['~/to', '~\\path\\.\\..\\..\\..\\to'],
            'dots'                         => ['./', './././././'],
            'dots (backslash)'             => ['./', '.\\.\\.\\.\\.\\'],
            'unc'                          => ['//server/share/file.txt', '//server/share/file.txt'],
            'unc (backslash)'              => ['//server/share/file.txt', '\\\\server\\share\\\\file.txt'],
            'unc (share)'                  => ['//./../path/file.txt', '\\\\.\\..\\path\\.\\\\file.txt'],
        ];
    }

    /**
     * @return array<string, array{list<string>, string}>
     */
    public static function dataProviderParts(): array {
        return [
            'empty'                          => [[''], ''],
            '///'                            => [['/'], '///'],
            '\\\\\\'                         => [['/'], '\\\\\\'],
            'root'                           => [['/'], '/'],
            'root (backslash)'               => [['/'], '\\'],
            'root path'                      => [['/', 'path', 'to'], '/path/to'],
            'root path (backslash)'          => [['/', 'path', 'to'], '\\path/to'],
            'home'                           => [['~/'], '~'],
            'home (slash)'                   => [['~/'], '~/'],
            'home (backslash)'               => [['~/'], '~\\'],
            'home (path)'                    => [['~/', 'path', 'to'], '~/path\\to'],
            'home (user)'                    => [['~user/'], '~user'],
            'home (user slash)'              => [['~user/', 'path', 'to'], '~user/path/to'],
            'home (user backslash)'          => [['~user/', 'path', 'to'], '~user\\path\\to'],
            'home (tilde)'                   => [['', '~'], './~'],
            'home (tilde path)'              => [['', '~path'], './~path'],
            'windows (disk)'                 => [['C:/'], 'C:'],
            'windows'                        => [['D:/'], 'D:\\'],
            'windows (slash)'                => [['D:/'], 'D:/'],
            'windows path (lowercase)'       => [['D:/', 'path', 'to'], 'd:\\path\\to'],
            'windows path'                   => [['D:/', 'path', 'to'], 'D:\\path\\to'],
            'windows path (slash)'           => [['D:/', 'path', 'to'], 'D:/path/to'],
            'windows relative'               => [['C:', 'path', 'to'], 'C:path\\to'],
            'windows relative (slash)'       => [['C:', 'path', 'to'], 'C:path/to'],
            'windows relative (lowercase)'   => [['C:', 'path', 'to'], 'c:path\\to'],
            'windows (malformed)'            => [['', '0:'], '0:'],
            'windows path (malformed)'       => [['', '0:', 'path'], '0:\\path'],
            'dot'                            => [[''], '.'],
            'dot (slash)'                    => [[''], './'],
            'dot (backslash)'                => [[''], '.\\'],
            'dot path (slash)'               => [['', 'path', 'to'], './path/to'],
            'dot path (backslash)'           => [['', 'path', 'to'], '.\\path\\to'],
            'dot dot'                        => [['', '..'], '..'],
            'dot dot (slash)'                => [['', '..'], '../'],
            'dot dot (backslash)'            => [['', '..'], '..\\'],
            'dot dot path (slash)'           => [['', '..', 'path', 'to'], '../path/to'],
            'dot dot path (backslash)'       => [['', '..', 'path', 'to'], '..\\path\\to'],
            'relative'                       => [['', 'path', 'to'], 'path/to'],
            'relative (backslash)'           => [['', 'path', 'to'], 'path\\to'],
            'relative dot'                   => [['', 'path', '.', '.', '', '.', '', 'to'], 'path/././/.//to'],
            'relative dot (backslash)'       => [['', 'path', '.', '.', '', '.', '', 'to'], 'path\\.\\.\\\\.\\\\to'],
            'relative dot dot'               => [
                ['', 'path', '.', '', 'to', '..', '..', '..', 'file'],
                'path/.//to/../../../file',
            ],
            'relative dot dot (backslash)'   => [
                ['', 'path', '.', '', 'to', '..', '..', '..', 'file'],
                'path\\.\\\\to\\..\\..\\..\\file',
            ],
            'absolute dot'                   => [['/', 'path', '.', '.', '', '.', '', 'to'], '/path/././/.//to'],
            'absolute dot (backslash)'       => [['/', 'path', '.', '.', '', '.', '', 'to'], '/path\\.\\.\\\\.\\\\to'],
            'absolute dot dot'               => [
                ['/', 'path', '.', '..', '..', '..', 'to'],
                '/path/./../../../to',
            ],
            'absolute dot dot(backslash)'    => [
                ['/', 'path', '.', '..', '..', '..', 'to'],
                '\\path\\.\\..\\..\\..\\to',
            ],
            'absolute home'                  => [
                ['~/', 'path', '.', '..', '..', '..', 'to'],
                '~/path/./../../../to',
            ],
            'absolute home (backslash)'      => [
                ['~/', 'path', '.', '..', '..', '..', 'to'],
                '~\\path\\.\\..\\..\\..\\to',
            ],
            'dots'                           => [['', '.', '.', '.', '.'], './././././'],
            'dots (backslash)'               => [['', '.', '.', '.', '.'], '.\\.\\.\\.\\.\\'],
            'unc'                            => [
                ['//server/share/', 'path', 'to', 'file.txt'],
                '//server/share/path/to/file.txt',
            ],
            'unc (backslash)'                => [
                ['//server/share/', '', 'path', 'to', 'file.txt'],
                '\\\\server\\share\\\\path\\to\\file.txt',
            ],
            'unc (share)'                    => [
                ['//./../', 'path', '.', '', 'to', 'file.txt'],
                '\\\\.\\..\\path\\.\\\\to\\file.txt',
            ],
            'unc (no share)'                 => [['//server/'], '//server'],
            'unc (no server)'                => [['//'], '//'],
            'file'                           => [['/', 'path', 'to'], '/path/to'],
            'directory'                      => [['/', 'path', 'to'], '/path/to/'],
            'directory multiple'             => [['/', 'path', 'to'], '/path/to//'],
            'directory multiple (backslash)' => [['/', 'path', 'to'], '\\path\\to\\'],
            'character'                      => [['', 'c'], './c'],
        ];
    }

    /**
     * @return array<string, array{?string, string, string}>
     */
    public static function dataProviderRelative(): array {
        return [
            'relative + absolute'        => [null, 'root', '/path'],
            'relative + relative'        => ['path', 'root', 'path'],
            'absolute + relative'        => ['path', '/root/path', 'path'],
            'type mismatch'              => [null, '/root/path/to/file.path', '~/root/path/to/file.path'],
            'same'                       => ['file.path', '/root/path/to/file.path', '/root/path/to/file.path'],
            'file + file'                => ['file', '/root/path/to/file.path', '/root/path/to/file'],
            'file + directory'           => ['directory/', '/root/path/to/file.path', '/root/path/to/directory/'],
            'file + file path'           => ['path/to/file', '/root/path/to/file.path', '/root/path/to/path/to/file'],
            'file + directory path'      => ['path/to/directory/', '/root/file.path', '/root/path/to/directory/'],
            'file + /file'               => ['../../../file.path', '/root/path/to/file.path', '/file.path'],
            'file + /directory'          => ['../../../directory/', '/root/path/to/file.path', '/directory/'],
            'directory + file'           => ['file', '/root/path/to/', '/root/path/to/file'],
            'directory + directory'      => ['directory/', '/root/path/to/', '/root/path/to/directory/'],
            'directory + file path'      => ['path/to/file', '/root/path/to/', '/root/path/to/path/to/file'],
            'directory + directory path' => ['path/to/directory/', '/root/', '/root/path/to/directory/'],
            'directory + /file'          => ['../../../file.path', '/root/path/to/', '/file.path'],
            'directory + /directory'     => ['../../../directory/', '/root/path/to/', '/directory/'],
            'normalization'              => ['file', '/root/./path/./to/./file.path', '\\root\\.\\path\\.\\to\\file'],
            'different root'             => [null, 'C:/root', 'D:/path/to/file.txt'],
        ];
    }

    /**
     * @return array<string, array{array{Type, string}, string, string}>
     */
    public static function dataProviderResolve(): array {
        return [
            'absolute + absolute'                                   => [
                [Type::Absolute, '/path/to/file.txt'],
                '/root/path',
                '/path/./to/././file.txt',
            ],
            'absolute + relative'                                   => [
                [Type::Absolute, '/root/path/to/file.txt'],
                '/root/path',
                'path/./to/././file.txt',
            ],
            'relative + absolute'                                   => [
                [Type::Absolute, '/path/to/file.txt'],
                'root/path',
                '/path/./to/././file.txt',
            ],
            'relative + relative'                                   => [
                [Type::Relative, 'root/path/to/file.txt'],
                'root/path',
                'path/./to/././file.txt',
            ],
            'absolute + windows absolute'                           => [
                [Type::WindowsAbsolute, 'C:/path/to/file.txt'],
                '/root/path',
                'C:/path/to/file.txt',
            ],
            'relative + windows absolute'                           => [
                [Type::WindowsAbsolute, 'C:/path/to/file.txt'],
                'root/path',
                'C:/path/to/file.txt',
            ],
            'absolute + windows relative'                           => [
                [Type::WindowsAbsolute, 'C:/path/to/file.txt'],
                '/root/path',
                'C:path/to/file.txt',
            ],
            'relative + windows relative'                           => [
                [Type::WindowsAbsolute, 'C:/path/to/file.txt'],
                'root/path',
                'C:path/to/file.txt',
            ],
            'windows absolute + windows relative (same drive)'      => [
                [Type::WindowsAbsolute, 'C:/root/path/to/file.txt'],
                'c:/root/path',
                'C:path/to/file.txt',
            ],
            'windows absolute + windows relative (different drive)' => [
                [Type::WindowsAbsolute, 'C:/path/to/file.txt'],
                'D:/root/path',
                'C:path/to/file.txt',
            ],
            'windows relative + windows relative (same drive)'      => [
                [Type::WindowsRelative, 'C:root/path/to/file.txt'],
                'c:root/path',
                'C:path/to/file.txt',
            ],
            'windows relative + windows relative (different drive)' => [
                [Type::WindowsAbsolute, 'C:/path/to/file.txt'],
                'D:root/path',
                'C:path/to/file.txt',
            ],
        ];
    }
    //</editor-fold>
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection   PhpMultipleClassesDeclarationsInOneFile
 * @phpstan-ignore class.disallowedSubtype (for test)
 */
class PathTest_Path extends Path {
    // empty
}
