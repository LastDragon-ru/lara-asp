<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Providers;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ExpectedValue::class)]
class ExpectedValueTest extends TestCase {
    public function testGetValue(): void {
        self::assertEquals(1, (new ExpectedValue(1))->getValue());
    }
}
