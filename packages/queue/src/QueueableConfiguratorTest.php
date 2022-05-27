<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue;

use DateInterval;
use Illuminate\Bus\Queueable;
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
            use Queueable;

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
            'delay' => 'PT2M',
        ]);

        $configurator->configure($queueable);

        self::assertEquals(60, $queueable->timeout);
        self::assertEquals(123, $queueable->tries ?? null);         // @phpstan-ignore-line
        self::assertEquals(345, $queueable->maxExceptions ?? null);
        self::assertInstanceOf(DateInterval::class, $queueable->delay);
        self::assertEquals('2', $queueable->delay->format('%i'));
    }
}
