<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use const PHP_INT_MAX;

/**
 * @internal
 */
#[CoversClass(Integer::class)]
final class IntegerTest extends TestCase {
    public function testAdd(): void {
        self::assertSame(12, Integer::add(10, 2));
        self::assertSame(PHP_INT_MAX, Integer::add(1, PHP_INT_MAX));
        self::assertSame(PHP_INT_MAX, Integer::add(PHP_INT_MAX, 1));
        self::assertSame(PHP_INT_MAX - 1, Integer::add(PHP_INT_MAX, -1));
        self::assertSame(PHP_INT_MAX - 1, Integer::add(-1, PHP_INT_MAX));
    }
}
