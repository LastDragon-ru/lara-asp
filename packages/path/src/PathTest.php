<?php declare(strict_types = 1);

namespace LastDragon_ru\Path;

use LastDragon_ru\Path\Package\TestCase;
use Mockery;
use Override;
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

    public function testPropertyNormalized(): void {
        self::assertTrue((new PathTest_Path('/any/path'))->normalized);
        self::assertTrue((new PathTest_Path('any/path'))->normalized);
        self::assertFalse((new PathTest_Path('./any/path'))->normalized);
        self::assertFalse((new PathTest_Path('././any/path'))->normalized);
        self::assertFalse((new PathTest_Path('./../any/path'))->normalized);
    }

    public function testResolveAbsolute(): void {
        $target   = new PathTest_Path('/to/absolute/./path');
        $relative = (new PathTest_Path('relative/path'));
        $absolute = (new PathTest_Path('/absolute/path'));

        self::assertSame('/to/absolute/path', (string) $relative->resolve($target));
        self::assertSame('/to/absolute/path', (string) $absolute->resolve($target));
    }

    public function testResolveRelative(): void {
        $to       = new PathTest_Path('to/../relative/./path');
        $relative = new PathTest_Path('relative/path');
        $absolute = new PathTest_Path('/absolute/path');

        self::assertSame('/absolute/relative/path', (string) $absolute->resolve($to));
        self::assertSame('relative/relative/path', (string) $relative->resolve($to));
    }

    public function testNormalized(): void {
        $path     = 'path/to';
        $instance = Mockery::mock(Path::class, [$path]);
        $instance->shouldAllowMockingProtectedMethods();
        $instance->makePartial();
        $instance
            ->shouldReceive('normalize')
            ->with($path)
            ->times(3)
            ->andReturn('normalized');

        $normalized = $instance->normalized();

        self::assertSame('normalized', (string) $normalized);
        self::assertNotSame($normalized, $instance->normalized());
    }

    public function testRelative(): void {
        self::assertSame(
            'to/file',
            (string) (new PathTest_Path('/any/path'))->relative(new PathTest_Path('/any/path/../to/file')),
        );
        self::assertSame(
            'path',
            (string) (new PathTest_Path('/any/path'))->relative(new PathTest_Path('/any/path')),
        );
        self::assertSame(
            'to/file',
            (string) (new PathTest_Path('/absolute/path'))->relative(new PathTest_Path('to/./file')),
        );
    }

    public function testPropertyRelative(): void {
        $path = new PathTest_Path('/any/path');

        self::assertSame(!$path->absolute, $path->relative);
    }

    #[DataProvider('dataProviderIsAbsolute')]
    public function testPropertyAbsolute(bool $expected, string $path): void {
        self::assertSame($expected, (new PathTest_Path($path))->absolute);
    }

    public function testFile(): void {
        $relative = (new PathTest_Path('relative/path/to'))->file('file');
        $absolute = (new PathTest_Path('/path/to'))->file('/file');

        self::assertSame('relative/path/file', (string) $relative);

        self::assertSame('/file', (string) $absolute);
    }

    public function testDirectory(): void {
        $relative = (new PathTest_Path('relative/path/to'))->directory('directory');
        $absolute = (new PathTest_Path('/path/to'))->directory('/directory');
        $null     = (new PathTest_Path('/path/to'))->directory();

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
        self::assertFalse(
            (new PathTest_Path('path/to/file'))->equals(
            // @phpstan-ignore class.disallowedSubtype (for test)
                new class('path/to/file') extends Path {
                    // path must be a subclass of
                },
            ),
        );
    }

    public function testMake(): void {
        self::assertEquals(new DirectoryPath(''), Path::make(''));
        self::assertEquals(new DirectoryPath('.'), Path::make('.'));
        self::assertEquals(new DirectoryPath('./'), Path::make('./'));
        self::assertEquals(new DirectoryPath('..'), Path::make('..'));
        self::assertEquals(new DirectoryPath('../'), Path::make('../'));
        self::assertEquals(new DirectoryPath('path/'), Path::make('path/'));
        self::assertEquals(new DirectoryPath('/path/'), Path::make('/path/'));
        self::assertEquals(new FilePath('path'), Path::make('path'));
        self::assertEquals(new FilePath('/path'), Path::make('/path'));
    }

    #[DataProvider('dataProviderIsAbsolute')]
    public function testIsAbsolute(bool $expected, string $path): void {
        self::assertSame($expected, (new PathTest_Path($path))->absolute);
    }

    #[DataProvider('dataProviderNormalize')]
    public function testNormalize(string $expected, string $path): void {
        self::assertSame($expected, PathTest_Path::normalize($path));
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<string, array{bool, string}>
     */
    public static function dataProviderIsAbsolute(): array {
        return [
            'empty'                      => [false, ''],
            'unix root'                  => [true, '/'],
            'unix root (backslash)'      => [true, '\\'],
            'unix root path'             => [true, '/path/to'],
            'unix root path (backslash)' => [true, '\\path/to'],
            'unix home'                  => [true, '~'],
            'unix home (slash)'          => [true, '~/'],
            'unix home (backslash)'      => [true, '~\\'],
            'win drive'                  => [true, 'C:'],
            'win root'                   => [true, 'D:\\'],
            'win root (slash)'           => [true, 'D:/'],
            'win root path'              => [true, 'D:\\path\\to'],
            'win root path (slash)'      => [true, 'D:/path/to'],
            'win malformed'              => [false, 'C:path\\to'],
            'url (https)'                => [false, 'https://example.com'],
            'url (mailto)'               => [false, 'mailto:example@example.com'],
            'dot'                        => [false, '.'],
            'dot (slash)'                => [false, './'],
            'dot (backslash)'            => [false, '.\\'],
            'dot path (slash)'           => [false, './path/to'],
            'dot path (backslash)'       => [false, '.\\path\\to'],
            'dot dot'                    => [false, '..'],
            'dot dot (slash)'            => [false, '../'],
            'dot dot (backslash)'        => [false, '..\\'],
            'dot dot path (slash)'       => [false, '../path/to'],
            'dot dot path (backslash)'   => [false, '..\\path\\to'],
            'relative'                   => [false, 'path/to'],
            'relative (backslash)'       => [false, 'path\\to'],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function dataProviderNormalize(): array {
        return [
            'empty'                          => ['', ''],
            'unix root'                      => ['/', '/'],
            'unix root (backslash)'          => ['/', '\\'],
            'unix root path'                 => ['/path/to', '/path/to'],
            'unix root path (backslash)'     => ['/path/to', '\\path/to'],
            'unix home'                      => ['~/', '~'],
            'unix home (slash)'              => ['~/', '~/'],
            'unix home (backslash)'          => ['~/', '~\\'],
            'win drive'                      => ['C:/', 'C:'],
            'win root'                       => ['D:/', 'D:\\'],
            'win root (slash)'               => ['D:/', 'D:/'],
            'win root path'                  => ['D:/path/to', 'D:\\path\\to'],
            'win root path (slash)'          => ['D:/path/to', 'D:/path/to'],
            'win malformed'                  => ['C:path/to', 'C:path\\to'],
            'dot'                            => ['', '.'],
            'dot (slash)'                    => ['', './'],
            'dot (backslash)'                => ['', '.\\'],
            'dot path (slash)'               => ['path/to', './path/to'],
            'dot path (backslash)'           => ['path/to', '.\\path\\to'],
            'dot dot'                        => ['..', '..'],
            'dot dot (slash)'                => ['..', '../'],
            'dot dot (backslash)'            => ['..', '..\\'],
            'dot dot path (slash)'           => ['../path/to', '../path/to'],
            'dot dot path (backslash)'       => ['../path/to', '..\\path\\to'],
            'relative'                       => ['path/to', 'path/to'],
            'relative (backslash)'           => ['path/to', 'path\\to'],
            'relative dot'                   => ['path/to', 'path/././/.//to'],
            'relative dot (backslash)'       => ['path/to', 'path\\.\\.\\\\.\\\\to'],
            'relative dot dot'               => ['../file', 'path/.//to/../../../file'],
            'relative dot dot (backslash)'   => ['../file', 'path\\.\\\\to\\..\\..\\..\\file'],
            'absolute dot'                   => ['/path/to', '/path/././/.//to'],
            'absolute dot (backslash)'       => ['/path/to', '/path\\.\\.\\\\.\\\\to'],
            'absolute dot dot'               => ['/to', '/path/./../../../to'],
            'absolute dot dot(backslash)'    => ['/to', '\\path\\.\\..\\..\\..\\to'],
            'absolute unix home'             => ['~/to', '~/path/./../../../to'],
            'absolute unix home (backslash)' => ['~/to', '~\\path\\.\\..\\..\\..\\to'],
            'starts with tilde'              => ['~path/to', '~path/to'],
            'starts with tilde (backslash)'  => ['~path/to', '~path\\to'],
            'dots'                           => ['', './././././'],
            'dots (backslash)'               => ['', '.\\.\\.\\.\\.\\'],
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
    #[Override]
    public static function normalize(string $path): string {
        return parent::normalize($path);
    }
}
