<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Utils;

use LastDragon_ru\LaraASP\Core\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function basename;
use function dirname;
use function str_replace;

/**
 * @internal
 * @deprecated 7.0.0
 */
#[CoversClass(Path::class)]
final class PathTest extends TestCase {
    public function testGetPath(): void {
        self::assertSame('/absolute/path/to/file', Path::getPath('any/path', '/absolute/path/./to/file'));
        self::assertSame('/absolute/path/to/file', Path::getPath('/absolute/path', 'to/./file'));
        self::assertEquals(
            str_replace('\\', '/', dirname(__FILE__).'/to/file'),
            Path::getPath(__FILE__, 'to/./file'),
        );
    }

    public function testGetRelativePath(): void {
        self::assertSame('../to/file', Path::getRelativePath('/any/path', '/any/path/../to/file'));
        self::assertSame('', Path::getRelativePath('/any/path', '/any/path'));
        self::assertSame('to/file', Path::getRelativePath('/absolute/path', 'to/./file'));
        self::assertSame(basename(__FILE__), Path::getRelativePath(__FILE__, __FILE__));
    }

    public function testJoin(): void {
        self::assertSame('/any/path', Path::join('/any/path'));
        self::assertSame('/any/path', Path::join('/any', 'path'));
        self::assertSame('/path', Path::join('/any', '..', 'path'));
        self::assertSame('any/path', Path::join('.', 'any', '.', 'path'));
        self::assertSame('../any/path', Path::join('..', 'any', '.', 'path'));
    }

    public function testNormalize(): void {
        self::assertSame('/any/path', Path::normalize('/any/path'));
        self::assertSame('any/path', Path::normalize('any/path'));
        self::assertSame('any/path', Path::normalize('./any/path'));
        self::assertSame('any/path', Path::normalize('././any/path'));
        self::assertSame('../any/path', Path::normalize('./../any/path'));
        self::assertSame('path', Path::normalize('./any/../path'));
        self::assertSame('', Path::normalize('./'));
        self::assertSame('', Path::normalize('.'));
        self::assertSame('..', Path::normalize('../'));
        self::assertSame('..', Path::normalize('..'));
        self::assertSame('path', Path::normalize('./any/../path/.'));
        self::assertSame('/', Path::normalize('/..'));
        self::assertSame('../any/path', Path::normalize('.\\..\\any\\path'));
        self::assertSame('any/path', Path::normalize('any\\path'));
        self::assertSame('/any/path', Path::normalize('/any/path/'));
        self::assertSame('any/path', Path::normalize('any/path/'));
    }

    public function testIsNormalized(): void {
        self::assertTrue(Path::isNormalized('/any/path'));
        self::assertTrue(Path::isNormalized('any/path'));
        self::assertFalse(Path::isNormalized('./any/path'));
        self::assertFalse(Path::isNormalized('././any/path'));
        self::assertFalse(Path::isNormalized('./../any/path'));
    }

    public function testIsFile(): void {
        self::assertTrue(Path::isFile(__FILE__));
        self::assertFalse(Path::isFile(__DIR__));
        self::assertFalse(Path::isFile('/path/to/file'));
    }

    public function testIsDirectory(): void {
        self::assertTrue(Path::isDirectory(__DIR__));
        self::assertFalse(Path::isDirectory(__FILE__));
        self::assertFalse(Path::isDirectory('/path/to/file'));
    }
}
