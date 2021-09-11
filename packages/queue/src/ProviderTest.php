<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue;

use LastDragon_ru\LaraASP\Queue\Contracts\ConfigurableQueueable;
use LastDragon_ru\LaraASP\Queue\Testing\Package\TestCase;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Queue\Provider
 */
class ProviderTest extends TestCase {
    /**
     * @covers ::registerConfigurator
     */
    public function testRegisterConfigurator(): void {
        $actual   = $this->app->make(ProviderTest_ConfigurableQueueable::class)->queue ?? null;
        $expected = 'test';

        $this->assertEquals($expected, $actual);
    }
}


// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ProviderTest_ConfigurableQueueable implements ConfigurableQueueable {
    /**
     * @inheritDoc
     */
    public function getQueueConfig(): array {
        return [
            'queue' => 'test',
        ];
    }
}

// @phpcs:enable
