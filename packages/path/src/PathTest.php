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
        $relative = (new PathTest_Path('relative/path'))->resolve($target);
        $absolute = (new PathTest_Path('/absolute/path'))->resolve($target);

        self::assertSame('/to/absolute/path', (string) $relative);
        self::assertTrue($relative->is(Type::Absolute));
        self::assertSame('/to/absolute/path', (string) $absolute);
        self::assertTrue($absolute->is(Type::Absolute));
    }

    public function testResolveRelative(): void {
        $target   = new PathTest_Path('to/../relative/./path');
        $relative = (new PathTest_Path('relative/path'))->resolve($target);
        $absolute = (new PathTest_Path('/absolute/path'))->resolve($target);

        self::assertSame('relative/relative/path', (string) $relative);
        self::assertTrue($relative->is(Type::Relative));
        self::assertSame('/absolute/relative/path', (string) $absolute);
        self::assertTrue($absolute->is(Type::Absolute));
    }

    public function testConcatHome(): void {
        $target   = new PathTest_Path('~/home');
        $relative = (new PathTest_Path('relative/path'))->concat($target);
        $absolute = (new PathTest_Path('/absolute/path'))->concat($target);

        self::assertSame('relative/~/home', (string) $relative);
        self::assertTrue($relative->is(Type::Relative));
        self::assertSame('/absolute/~/home', (string) $absolute);
        self::assertTrue($absolute->is(Type::Absolute));
    }

    public function testConcatAbsolute(): void {
        $target   = new PathTest_Path('/to/absolute/./path');
        $relative = (new PathTest_Path('relative/path'))->concat($target);
        $absolute = (new PathTest_Path('/absolute/path'))->concat($target);

        self::assertSame('relative/to/absolute/path', (string) $relative);
        self::assertTrue($relative->is(Type::Relative));
        self::assertSame('/absolute/to/absolute/path', (string) $absolute);
        self::assertTrue($absolute->is(Type::Absolute));
    }

    public function testConcatRelative(): void {
        $target   = new PathTest_Path('to/../relative/./path');
        $relative = (new PathTest_Path('relative/path'))->concat($target);
        $absolute = (new PathTest_Path('/absolute/path'))->concat($target);

        self::assertSame('relative/relative/path', (string) $relative);
        self::assertTrue($relative->is(Type::Relative));
        self::assertSame('/absolute/relative/path', (string) $absolute);
        self::assertTrue($absolute->is(Type::Absolute));
    }

    public function testNormalized(): void {
        $path     = 'path/to';
        $instance = Mockery::mock(Path::class, [$path]);
        $instance->shouldAllowMockingProtectedMethods();
        $instance->makePartial();
        $instance
            ->shouldReceive('sync')
            ->twice()
            ->andReturnUsing(static fn ($path) => $path);
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
        $root = new PathTest_Path('/root/path/to/file.path');

        self::assertSame(
            'file.path',
            (string) $root->relative($root),
        );
        self::assertSame(
            'file',
            (string) $root->relative(new FilePath('/root/path/to/file')),
        );
        self::assertSame(
            'directory/',
            (string) $root->relative(new DirectoryPath('/root/path/to/directory')),
        );
        self::assertSame(
            'path/to/file',
            (string) $root->relative(new FilePath('/root/path/to/path/to/file')),
        );
        self::assertSame(
            'path/to/directory/',
            (string) $root->relative(new DirectoryPath('/root/path/to/path/to/directory')),
        );
        self::assertSame(
            '../../../directory/',
            (string) $root->relative(new DirectoryPath('/directory')),
        );
        self::assertSame(
            '../../../file',
            (string) $root->relative(new FilePath('/file')),
        );
        self::assertSame(
            '../../../file',
            (string) $root->relative(new FilePath('/./file')),
        );
    }

    public function testRelativeRootRelative(): void {
        $root = new PathTest_Path('./');
        $path = new PathTest_Path('/path');

        self::assertNull($root->relative($path));
    }

    public function testRelativePathRelative(): void {
        $root = new PathTest_Path('/root/path');
        $path = new PathTest_Path('path');

        self::assertSame($path, $root->relative($path)); // @phpstan-ignore staticMethod.impossibleType
    }

    #[DataProvider('dataProviderType')]
    public function testPropertyType(Type $expected, string $path): void {
        self::assertSame($expected, (new PathTest_Path($path))->type);
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

    #[DataProvider('dataProviderNormalize')]
    public function testNormalize(string $expected, string $path): void {
        self::assertSame($expected, PathTest_Path::normalize($path));
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
     * @return array<string, array{Type, string}>
     */
    public static function dataProviderType(): array {
        return [
            'empty'                      => [Type::Relative, ''],
            'unix root'                  => [Type::Absolute, '/'],
            'unix root (backslash)'      => [Type::Absolute, '\\'],
            'unix root path'             => [Type::Absolute, '/path/to'],
            'unix root path (backslash)' => [Type::Absolute, '\\path/to'],
            'unix home'                  => [Type::Home, '~'],
            'unix home (slash)'          => [Type::Home, '~/'],
            'unix home (backslash)'      => [Type::Home, '~\\'],
            'unix home (path)'           => [Type::Home, '~/path\\to'],
            'win drive'                  => [Type::Absolute, 'C:'],
            'win root'                   => [Type::Absolute, 'D:\\'],
            'win root (slash)'           => [Type::Absolute, 'D:/'],
            'win root path'              => [Type::Absolute, 'D:\\path\\to'],
            'win root path (slash)'      => [Type::Absolute, 'D:/path/to'],
            'win malformed'              => [Type::Relative, 'C:path\\to'],
            'url (https)'                => [Type::Relative, 'https://example.com'],
            'url (mailto)'               => [Type::Relative, 'mailto:example@example.com'],
            'dot'                        => [Type::Relative, '.'],
            'dot (slash)'                => [Type::Relative, './'],
            'dot (backslash)'            => [Type::Relative, '.\\'],
            'dot path (slash)'           => [Type::Relative, './path/to'],
            'dot path (backslash)'       => [Type::Relative, '.\\path\\to'],
            'dot dot'                    => [Type::Relative, '..'],
            'dot dot (slash)'            => [Type::Relative, '../'],
            'dot dot (backslash)'        => [Type::Relative, '..\\'],
            'dot dot path (slash)'       => [Type::Relative, '../path/to'],
            'dot dot path (backslash)'   => [Type::Relative, '..\\path\\to'],
            'relative'                   => [Type::Relative, 'path/to'],
            'relative (backslash)'       => [Type::Relative, 'path\\to'],
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
            'unix home (path)'               => ['~/path/to', '~/path\\to'],
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
