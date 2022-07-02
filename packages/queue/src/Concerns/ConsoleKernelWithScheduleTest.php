<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Concerns;

use Illuminate\Foundation\Console\Kernel;
use LastDragon_ru\LaraASP\Queue\Testing\Package\TestCase;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Queue\Concerns\ConsoleKernelWithSchedule
 */
class ConsoleKernelWithScheduleTest extends TestCase {
    public function testImpl(): void {
        self::assertNotEmpty($this->app->make(ConsoleKernelWithScheduleTest_Impl::class));
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ConsoleKernelWithScheduleTest_Impl extends Kernel {
    use ConsoleKernelWithSchedule;
}
