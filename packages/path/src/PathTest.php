<?php declare(strict_types = 1);

namespace LastDragon_ru\Path;

use LastDragon_ru\Path\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Path::class)]
final class PathTest extends TestCase {
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
        self::assertSame('/any/path', (string) (new PathTest_Path('/any/path'))->normalized());
        self::assertSame('any/path', (string) (new PathTest_Path('any/path'))->normalized());
        self::assertSame('any/path', (string) (new PathTest_Path('./any/path'))->normalized());
        self::assertSame('any/path', (string) (new PathTest_Path('././any/path'))->normalized());
        self::assertSame('../any/path', (string) (new PathTest_Path('./../any/path'))->normalized());
        self::assertSame('path', (string) (new PathTest_Path('./any/../path'))->normalized());
        self::assertSame('', (string) (new PathTest_Path('./'))->normalized());
        self::assertSame('', (string) (new PathTest_Path('.'))->normalized());
        self::assertSame('..', (string) (new PathTest_Path('../'))->normalized());
        self::assertSame('..', (string) (new PathTest_Path('..'))->normalized());
        self::assertSame('path', (string) (new PathTest_Path('./any/../path/.'))->normalized());
        self::assertSame('/', (string) (new PathTest_Path('/..'))->normalized());
        self::assertSame('../any/path', (string) (new PathTest_Path('.\\..\\any\\path'))->normalized());
        self::assertSame('any/path', (string) (new PathTest_Path('any\\path'))->normalized());
        self::assertSame('/any/path', (string) (new PathTest_Path('/any/path/'))->normalized());
        self::assertSame('any/path', (string) (new PathTest_Path('any/path/'))->normalized());
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
        self::assertFalse((new PathTest_Path('/any/path'))->relative);
        self::assertTrue((new PathTest_Path('any/path'))->relative);
        self::assertTrue((new PathTest_Path('./any/path'))->relative);
        self::assertTrue((new PathTest_Path('././any/path'))->relative);
        self::assertTrue((new PathTest_Path('./../any/path'))->relative);
    }

    public function testPropertyAbsolute(): void {
        self::assertTrue((new PathTest_Path('/any/path'))->absolute);
        self::assertFalse((new PathTest_Path('any/path'))->absolute);
        self::assertFalse((new PathTest_Path('./any/path'))->absolute);
        self::assertFalse((new PathTest_Path('././any/path'))->absolute);
        self::assertFalse((new PathTest_Path('./../any/path'))->absolute);
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
                new class('path/to/file') extends Path {
                    // path must be a subclass of
                },
            ),
        );
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class PathTest_Path extends Path {
    // empty
}
