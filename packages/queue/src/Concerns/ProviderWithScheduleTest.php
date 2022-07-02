<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Concerns;

use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Queue\Testing\Package\TestCase;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Queue\Concerns\ProviderWithSchedule
 */
class ProviderWithScheduleTest extends TestCase {
    public function testImpl(): void {
        self::assertNotEmpty($this->app->make(ProviderWithScheduleTest_Impl::class, [
            'app' => $this->app,
        ]));
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ProviderWithScheduleTest_Impl extends ServiceProvider {
    use ProviderWithSchedule;
}
