<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Path;

use LastDragon_ru\LaraASP\Core\Testing\Package\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function basename;

/**
 * @internal
 */
#[CoversClass(Path::class)]
final class PathTest extends TestCase {
    public function testToString(): void {
        $path   = '/a/b/c';
        $object = new PathTest_Path($path);

        self::assertEquals($path, (string) $object);
    }

    public function testGetName(): void {
        $path   = '/a/b/c';
        $object = new PathTest_Path($path);

        self::assertEquals(basename($path), $object->getName());
    }

    public function testGetPathToAbsolute(): void {
        $to       = new PathTest_Path('/to/absolute/./path');
        $relative = (new PathTest_Path('relative/path'))->getPath($to);
        $absolute = (new PathTest_Path('/absolute/path'))->getPath($to);

        self::assertNotSame($to, $relative);
        self::assertEquals('/to/absolute/path', (string) $relative);

        self::assertNotSame($to, $absolute);
        self::assertEquals('/to/absolute/path', (string) $absolute);
    }

    public function testGetPathToRelative(): void {
        $fromRelative = new PathTest_Path('relative/path');
        $fromAbsolute = new PathTest_Path('/absolute/path');
        $to           = new PathTest_Path('to/../relative/./path');

        self::assertEquals('/absolute/path/relative/path', (string) $fromAbsolute->getPath($to));
        self::assertEquals('relative/path/relative/path', (string) $fromRelative->getPath($to));
    }

    public function testGetNormalizedPath(): void {
        self::assertEquals('/any/path', (string) (new PathTest_Path('/any/path'))->getNormalizedPath());
        self::assertEquals('any/path', (string) (new PathTest_Path('any/path'))->getNormalizedPath());
        self::assertEquals('any/path', (string) (new PathTest_Path('./any/path'))->getNormalizedPath());
        self::assertEquals('any/path', (string) (new PathTest_Path('././any/path'))->getNormalizedPath());
        self::assertEquals('../any/path', (string) (new PathTest_Path('./../any/path'))->getNormalizedPath());
        self::assertEquals('path', (string) (new PathTest_Path('./any/../path'))->getNormalizedPath());
        self::assertEquals('', (string) (new PathTest_Path('./'))->getNormalizedPath());
        self::assertEquals('', (string) (new PathTest_Path('.'))->getNormalizedPath());
        self::assertEquals('..', (string) (new PathTest_Path('../'))->getNormalizedPath());
        self::assertEquals('..', (string) (new PathTest_Path('..'))->getNormalizedPath());
        self::assertEquals('path', (string) (new PathTest_Path('./any/../path/.'))->getNormalizedPath());
        self::assertEquals('/', (string) (new PathTest_Path('/..'))->getNormalizedPath());
        self::assertEquals('../any/path', (string) (new PathTest_Path('.\\..\\any\\path'))->getNormalizedPath());
        self::assertEquals('any/path', (string) (new PathTest_Path('any\\path'))->getNormalizedPath());
        self::assertEquals('/any/path', (string) (new PathTest_Path('/any/path/'))->getNormalizedPath());
        self::assertEquals('any/path', (string) (new PathTest_Path('any/path/'))->getNormalizedPath());
    }

    public function testIsNormalized(): void {
        self::assertTrue((new PathTest_Path('/any/path'))->isNormalized());
        self::assertTrue((new PathTest_Path('any/path'))->isNormalized());
        self::assertFalse((new PathTest_Path('./any/path'))->isNormalized());
        self::assertFalse((new PathTest_Path('././any/path'))->isNormalized());
        self::assertFalse((new PathTest_Path('./../any/path'))->isNormalized());
    }

    public function testGetRelativePath(): void {
        self::assertEquals(
            '../to/file',
            (string) (new PathTest_Path('/any/path'))->getRelativePath(new PathTest_Path('/any/path/../to/file')),
        );
        self::assertEquals(
            '',
            (string) (new PathTest_Path('/any/path'))->getRelativePath(new PathTest_Path('/any/path')),
        );
        self::assertEquals(
            'to/file',
            (string) (new PathTest_Path('/absolute/path'))->getRelativePath(new PathTest_Path('to/./file')),
        );
        self::assertEquals(
            basename(__FILE__),
            (string) (new PathTest_Path(__DIR__))->getRelativePath(new PathTest_Path(__FILE__)),
        );
    }

    public function testIsRelative(): void {
        self::assertFalse((new PathTest_Path('/any/path'))->isRelative());
        self::assertTrue((new PathTest_Path('any/path'))->isRelative());
        self::assertTrue((new PathTest_Path('./any/path'))->isRelative());
        self::assertTrue((new PathTest_Path('././any/path'))->isRelative());
        self::assertTrue((new PathTest_Path('./../any/path'))->isRelative());
    }

    public function testIsAbsolute(): void {
        self::assertTrue((new PathTest_Path('/any/path'))->isAbsolute());
        self::assertFalse((new PathTest_Path('any/path'))->isAbsolute());
        self::assertFalse((new PathTest_Path('./any/path'))->isAbsolute());
        self::assertFalse((new PathTest_Path('././any/path'))->isAbsolute());
        self::assertFalse((new PathTest_Path('./../any/path'))->isAbsolute());
    }

    public function testGetFilePath(): void {
        $relative = (new PathTest_Path('relative/path/to'))->getFilePath('file');
        $absolute = (new PathTest_Path('/path/to'))->getFilePath('/file');

        self::assertEquals('relative/path/to/file', (string) $relative);

        self::assertEquals('/file', (string) $absolute);
    }

    public function testGetDirectoryPath(): void {
        $relative = (new PathTest_Path('relative/path/to'))->getDirectoryPath('directory');
        $absolute = (new PathTest_Path('/path/to'))->getDirectoryPath('/directory');
        $null     = (new PathTest_Path('/path/to'))->getDirectoryPath();

        self::assertEquals('relative/path/to/directory', (string) $relative);

        self::assertEquals('/directory', (string) $absolute);
        self::assertEquals('/path', (string) $null);
    }

    public function testIsEqual(): void {
        self::assertTrue((new PathTest_Path('path/to/file'))->isEqual(new PathTest_Path('path/to/file')));
        self::assertTrue((new PathTest_Path('path/to/file'))->isEqual(new PathTest_Path('path/./to/./file')));
        self::assertTrue(
            (new PathTest_Path('path/to/file'))->isEqual(
                new class('path/to/file') extends PathTest_Path {
                    // empty
                },
            ),
        );
        self::assertFalse((new PathTest_Path('path/to/file'))->isEqual(new PathTest_Path('path/to')));
        self::assertFalse(
            (new PathTest_Path('path/to/file'))->isEqual(
                new class('path/to/file') extends Path {
                    #[Override]
                    public function getParentPath(): DirectoryPath {
                        return $this->getDirectoryPath('..');
                    }

                    #[Override]
                    protected function getDirectory(): DirectoryPath {
                        return new DirectoryPath($this->path);
                    }
                },
            ),
        );
    }

    public function testIsMatch(): void {
        $path = new PathTest_Path('path/to/file.md');

        self::assertTrue($path->isMatch('#[^/]+\.md$#'));
        self::assertFalse($path->isMatch('#[^/]+\.txt$#'));
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class PathTest_Path extends Path {
    #[Override]
    public function getParentPath(): DirectoryPath {
        return $this->getDirectoryPath('..');
    }

    #[Override]
    protected function getDirectory(): DirectoryPath {
        return new DirectoryPath($this->path);
    }
}
