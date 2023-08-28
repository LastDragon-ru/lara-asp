<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter;

use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\Formatter\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Provider::class)]
class ProviderTest extends TestCase {
    public function testRegister(): void {
        self::assertSame(
            Container::getInstance()->make(Formatter::class),
            Container::getInstance()->make(Formatter::class),
        );
    }
}
