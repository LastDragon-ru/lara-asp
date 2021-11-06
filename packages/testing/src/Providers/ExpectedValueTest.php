<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Providers;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Testing\Providers\ExpectedValue
 */
class ExpectedValueTest extends TestCase {
    /**
     * @covers ::getValue
     */
    public function testGetValue(): void {
        self::assertEquals(1, (new ExpectedValue(1))->getValue());
    }
}
