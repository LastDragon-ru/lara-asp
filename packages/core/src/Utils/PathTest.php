<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Utils;

use LastDragon_ru\LaraASP\Core\Testing\Package\TestCase;
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
        self::assertEquals('/absolute/path/to/file', Path::getPath('any/path', '/absolute/path/./to/file'));
        self::assertEquals('/absolute/path/to/file', Path::getPath('/absolute/path', 'to/./file'));
        self::assertEquals(
            str_replace('\\', '/', dirname(__FILE__).'/to/file'),
            Path::getPath(__FILE__, 'to/./file'),
        );
    }

    public function testGetRelativePath(): void {
        self::assertEquals('../to/file', Path::getRelativePath('/any/path', '/any/path/../to/file'));
        self::assertEquals('', Path::getRelativePath('/any/path', '/any/path'));
        self::assertEquals('to/file', Path::getRelativePath('/absolute/path', 'to/./file'));
        self::assertEquals(basename(__FILE__), Path::getRelativePath(__FILE__, __FILE__));
    }

    public function testJoin(): void {
        self::assertEquals('/any/path', Path::join('/any/path'));
        self::assertEquals('/any/path', Path::join('/any', 'path'));
        self::assertEquals('/path', Path::join('/any', '..', 'path'));
        self::assertEquals('any/path', Path::join('.', 'any', '.', 'path'));
        self::assertEquals('../any/path', Path::join('..', 'any', '.', 'path'));
    }

    public function testNormalize(): void {
        self::assertEquals('/any/path', Path::normalize('/any/path'));
        self::assertEquals('any/path', Path::normalize('any/path'));
        self::assertEquals('any/path', Path::normalize('./any/path'));
        self::assertEquals('any/path', Path::normalize('././any/path'));
        self::assertEquals('../any/path', Path::normalize('./../any/path'));
        self::assertEquals('path', Path::normalize('./any/../path'));
        self::assertEquals('', Path::normalize('./'));
        self::assertEquals('', Path::normalize('.'));
        self::assertEquals('..', Path::normalize('../'));
        self::assertEquals('..', Path::normalize('..'));
        self::assertEquals('path', Path::normalize('./any/../path/.'));
        self::assertEquals('/', Path::normalize('/..'));
        self::assertEquals('../any/path', Path::normalize('.\\..\\any\\path'));
        self::assertEquals('any/path', Path::normalize('any\\path'));
        self::assertEquals('/any/path', Path::normalize('/any/path/'));
        self::assertEquals('any/path', Path::normalize('any/path/'));
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
