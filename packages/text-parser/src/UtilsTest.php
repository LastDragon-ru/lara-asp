<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser;

use LastDragon_ru\TextParser\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Utils::class)]
final class UtilsTest extends TestCase {
    public function testToString(): void {
        self::assertSame('abc', Utils::toString(['a', 'b', 'c']));
        self::assertSame('a,b,c', Utils::toString(['a', 'b', 'c'], ','));
    }
}
