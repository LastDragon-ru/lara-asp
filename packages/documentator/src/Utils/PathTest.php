<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function dirname;
use function str_replace;

/**
 * @internal
 */
#[CoversClass(Path::class)]
class PathTest extends TestCase {
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
        self::assertEquals('to/file', Path::getRelativePath('/absolute/path', 'to/./file'));
    }
}
