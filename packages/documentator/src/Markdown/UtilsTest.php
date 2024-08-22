<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown;

use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Utils::class)]
final class UtilsTest extends TestCase {
    public function testIsPathRelative(): void {
        // Nope
        self::assertFalse(Utils::isPathRelative('tel:+70000000000'));
        self::assertFalse(Utils::isPathRelative('urn:example.example'));
        self::assertFalse(Utils::isPathRelative('//example.com/'));
        self::assertFalse(Utils::isPathRelative('https://example.com/'));
        self::assertFalse(Utils::isPathRelative('mailto:mail@example.com'));
        self::assertFalse(Utils::isPathRelative('/path/to/file.md'));

        // Yep
        self::assertTrue(Utils::isPathRelative('.'));
        self::assertTrue(Utils::isPathRelative('..'));
        self::assertTrue(Utils::isPathRelative('path/to/file.md'));
        self::assertTrue(Utils::isPathRelative('./path/to/file.md'));
        self::assertTrue(Utils::isPathRelative('?query'));
        self::assertTrue(Utils::isPathRelative('#fragment'));
    }

    public function testIsPathToSelf(): void {
        // Nope
        self::assertFalse(Utils::isPathToSelf('..'));
        self::assertFalse(Utils::isPathToSelf('file.md'));
        self::assertFalse(Utils::isPathToSelf('../path/to/file.txt'));
        self::assertFalse(Utils::isPathToSelf('http:://example.com/'));

        // Yep
        self::assertTrue(Utils::isPathToSelf('.'));
        self::assertTrue(Utils::isPathToSelf('./'));
        self::assertTrue(Utils::isPathToSelf('?query'));
        self::assertTrue(Utils::isPathToSelf('#fragment'));

        // Yep if match to Document file name
        $a = new Document('', '/path/to/a.md');
        $b = new Document('', '/path/to/b.md');

        self::assertTrue(Utils::isPathToSelf('a.md', $a));
        self::assertTrue(Utils::isPathToSelf('./a.md', $a));
        self::assertFalse(Utils::isPathToSelf('/a.md', $a));
        self::assertFalse(Utils::isPathToSelf('../a.md', $a));
        self::assertFalse(Utils::isPathToSelf('a.md', $b));
    }
}
