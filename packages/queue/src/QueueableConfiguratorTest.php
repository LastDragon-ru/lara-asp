<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue;

use LastDragon_ru\LaraASP\Queue\Contracts\ConfigurableQueueable;
use LastDragon_ru\LaraASP\Queue\Testing\Package\TestCase;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Queue\QueueableConfigurator
 */
class QueueableConfiguratorTest extends TestCase {
    /**
     * @covers ::configure
     */
    public function testConfigure(): void {
        $configurator = $this->app->make(QueueableConfigurator::class);
        $queueable    = new class() implements ConfigurableQueueable {
            public int $timeout       = 60;
            public int $maxExceptions = 123;

            /**
             * @inheritDoc
             */
            public function getQueueConfig(): array {
                return [
                    'maxExceptions' => 345,
                ];
            }
        };

        $this->setQueueableConfig($queueable, [
            'tries' => 123,
        ]);

        $configurator->configure($queueable);

        $this->assertEquals(60, $queueable->timeout);
        $this->assertEquals(123, $queueable->tries ?? null);         // @phpstan-ignore-line
        $this->assertEquals(345, $queueable->maxExceptions ?? null); // @phpstan-ignore-line
    }
}
