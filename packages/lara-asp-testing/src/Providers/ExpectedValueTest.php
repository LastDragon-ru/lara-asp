<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Providers;

use LastDragon_ru\LaraASP\Testing\Testing\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(ExpectedValue::class)]
final class ExpectedValueTest extends TestCase {
    public function testGetValue(): void {
        self::assertSame(1, (new ExpectedValue(1))->getValue());
    }
}
