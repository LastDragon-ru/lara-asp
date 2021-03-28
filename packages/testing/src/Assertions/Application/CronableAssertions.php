<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions\Application;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Config\Repository;
use LastDragon_ru\LaraASP\Core\Utils\ConfigMerger;
use LastDragon_ru\LaraASP\Queue\Configs\CronableConfig;
use LastDragon_ru\LaraASP\Queue\Contracts\Cronable;

use function array_filter;
use function count;
use function is_subclass_of;
use function sprintf;
use function str_contains;

/**
 * @required {@link \Illuminate\Foundation\Testing\TestCase}
 *
 * @property-read \Illuminate\Foundation\Application $app
 *
 * @mixin \PHPUnit\Framework\Assert
 */
trait CronableAssertions {
    /**
     * Asserts that {@link \LastDragon_ru\LaraASP\Queue\Contracts\Cronable} is registered.
     *
     * @param class-string<\LastDragon_ru\LaraASP\Queue\Contracts\Cronable> $cronable
     */
    protected function assertCronableRegistered(string $cronable, string $message = ''): void {
        $this->assertTrue(
            is_subclass_of($cronable, Cronable::class, true),
            sprintf('The `%s` must be instance of `%s`.', $cronable, Cronable::class),
        );

        $this->setQueueableConfig($cronable, [
            CronableConfig::Enabled => true,
        ]);

        $message  = $message ?: sprintf('The `%s` is not registered as scheduled job.', $cronable);
        $schedule = $this->app->make(Schedule::class);
        $events   = array_filter($schedule->events(), static function (Event $event) use ($cronable): bool {
            return str_contains($event->description ?? '', $cronable);
        });

        $this->assertEquals(1, count($events), $message);
    }

    /**
     * @param class-string<\LastDragon_ru\LaraASP\Queue\Contracts\ConfigurableQueueable> $queueable
     * @param array<string, mixed>                                                       $settings
     */
    protected function setQueueableConfig(string $queueable, array $settings): void {
        $config = $this->app->make(Repository::class);
        $merger = new ConfigMerger();
        $key    = sprintf('queue.queueables.%s', $queueable);
        $target = [ConfigMerger::Strict => false] + (array) $config->get($key);

        $config->set($key, $merger->merge($target, $settings));
    }
}
