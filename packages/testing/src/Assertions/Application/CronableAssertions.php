<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions\Application;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Application;
use LastDragon_ru\LaraASP\Core\Utils\ConfigMerger;
use LastDragon_ru\LaraASP\Queue\Contracts\ConfigurableQueueable;
use LastDragon_ru\LaraASP\Queue\Contracts\Cronable;
use PHPUnit\Framework\Assert;

use function array_filter;
use function count;
use function is_object;
use function method_exists;
use function sprintf;
use function str_contains;

/**
 * @required {@link \Illuminate\Foundation\Testing\TestCase}
 *
 * @property-read Application $app
 *
 * @mixin Assert
 */
trait CronableAssertions {
    /**
     * Asserts that {@link \LastDragon_ru\LaraASP\Queue\Contracts\Cronable} is registered.
     *
     * @param class-string<Cronable> $cronable
     */
    protected function assertCronableRegistered(string $cronable, string $message = ''): void {
        $message  = $message ?: sprintf('The `%s` is not registered as scheduled job.', $cronable);
        $expected = $this->isCronableRegistered($cronable);

        self::assertTrue($expected, $message);
    }

    /**
     * @param ConfigurableQueueable|class-string<ConfigurableQueueable> $queueable
     * @param array<string, mixed>                                      $settings
     */
    protected function setQueueableConfig(ConfigurableQueueable|string $queueable, array $settings): void {
        $config = $this->app->make(Repository::class);
        $merger = new ConfigMerger();
        $key    = sprintf('queue.queueables.%s', is_object($queueable) ? $queueable::class : $queueable);
        $target = [ConfigMerger::Strict => false] + (array) $config->get($key);

        $config->set($key, $merger->merge($target, $settings));
    }

    /**
     * @param class-string<Cronable> $cronable
     */
    protected function isCronableRegistered(string $cronable): bool {
        $schedule = $this->app->make(Schedule::class);
        $expected = method_exists($cronable, 'displayName')
            ? $this->app->make($cronable)->displayName()
            : $cronable;
        $events   = array_filter($schedule->events(), static function (Event $event) use ($expected): bool {
            return str_contains("{$event->description}", $expected);
        });

        return count($events) === 1;
    }
}
