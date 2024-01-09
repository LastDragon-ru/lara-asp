<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Utils;

use DateTimeZone;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\ShouldQueue;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Core\Contracts\Schedulable;

use function array_intersect_key;
use function is_callable;
use function is_int;
use function sprintf;

/**
 * @phpstan-type SchedulableSettings array{
 *      cron: string,
 *      enabled?: bool,
 *      timezone?: DateTimeZone|string|null,
 *      inMaintenanceMode?: bool|null,
 *      withoutOverlapping?: int<0, max>|true|null,
 *      }
 */
class Scheduler {
    public function __construct() {
        // empty
    }

    /**
     * @param class-string $class
     */
    public function register(Schedule $schedule, string $class): bool {
        // Config
        $instance = Container::getInstance()->make($class);
        $settings = $this->getSettings($class, $instance);

        // Enabled?
        if (!($settings['enabled'] ?? true) || !$settings['cron']) {
            return false;
        }

        // Register
        $event = match (true) {
            $instance instanceof ShouldQueue => $schedule->job($instance),
            is_callable($instance)           => $schedule->call($instance),
            default                          => throw new InvalidArgumentException(
                sprintf(
                    'The `%s` must be a callable.',
                    $class,
                ),
            ),
        };

        $event->cron($settings['cron']);

        if (isset($settings['timezone'])) {
            $event->timezone($settings['timezone']);
        }

        if (isset($settings['inMaintenanceMode']) && $settings['inMaintenanceMode']) {
            $event->evenInMaintenanceMode();
        }

        if (isset($settings['withoutOverlapping'])) {
            if (is_int($settings['withoutOverlapping'])) {
                $event->withoutOverlapping($settings['withoutOverlapping']);
            } else {
                $event->withoutOverlapping();
            }
        }

        // Return
        return true;
    }

    /**
     * @param class-string $class
     *
     * @return SchedulableSettings
     */
    protected function getSettings(string $class, object $instance): array {
        $default  = [
            'cron'               => '',
            'enabled'            => true,
            'timezone'           => null,
            'inMaintenanceMode'  => false,
            'withoutOverlapping' => null,
        ];
        $settings = $instance instanceof Schedulable ? $instance->getSchedule() : [];
        $settings = array_intersect_key($settings + $default, $default);

        return $settings;
    }
}
