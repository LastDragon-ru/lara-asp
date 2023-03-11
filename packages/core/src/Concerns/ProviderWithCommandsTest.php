<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Concerns;

use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Testing\Package\TestCase;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\Core\Concerns\ProviderWithCommands
 */
class ProviderWithCommandsTest extends TestCase {
    public function testImpl(): void {
        self::assertNotEmpty($this->app->make(ProviderWithCommandsTest_Impl::class, [
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
class ProviderWithCommandsTest_Impl extends ServiceProvider {
    use ProviderWithCommands;
}
