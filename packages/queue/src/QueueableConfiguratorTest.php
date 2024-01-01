<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue;

use AllowDynamicProperties;
use DateInterval;
use Illuminate\Bus\Queueable;
use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\Queue\Contracts\ConfigurableQueueable;
use LastDragon_ru\LaraASP\Queue\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Queue\Testing\Package\WithQueueableConfig;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(QueueableConfigurator::class)]
class QueueableConfiguratorTest extends TestCase {
    use WithQueueableConfig;

    public function testConfigure(): void {
        $configurator = Container::getInstance()->make(QueueableConfigurator::class);
        $queueable    = new QueueableConfiguratorTest_ConfigurableQueueable();

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

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
#[AllowDynamicProperties]
class QueueableConfiguratorTest_ConfigurableQueueable implements ConfigurableQueueable {
    use Queueable;

    public int $timeout       = 60;
    public int $maxExceptions = 123;

    /**
     * @inheritDoc
     */
    #[Override]
    public function getQueueConfig(): array {
        return [
            'maxExceptions' => 345,
        ];
    }
}
