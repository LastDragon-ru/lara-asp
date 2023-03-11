<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Providers;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\Testing\Providers\ExpectedValue
 */
class ExpectedValueTest extends TestCase {
    public function testGetValue(): void {
        self::assertEquals(1, (new ExpectedValue(1))->getValue());
    }
}
