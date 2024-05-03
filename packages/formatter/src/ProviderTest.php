<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter;

use LastDragon_ru\LaraASP\Formatter\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Provider::class)]
final class ProviderTest extends TestCase {
    public function testRegister(): void {
        self::assertSame(
            $this->app()->make(Formatter::class),
            $this->app()->make(Formatter::class),
        );
    }
}
