<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions\Application;

use Illuminate\Console\Application;
use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Container\Container;
use PHPUnit\Framework\Assert;

use function array_filter;
use function count;
use function sprintf;

trait ScheduleAssertions {
    /**
     * Asserts that Schedule contains task.
     */
    public static function assertScheduled(string $expected, string $message = ''): void {
        $message   = $message ?: sprintf('The `%s` is not scheduled.', $expected);
        $scheduled = self::isScheduledEvent($expected);

        Assert::assertTrue($scheduled, $message);
    }

    /**
     * @internal
     */
    private static function isScheduledEvent(string $task): bool {
        return count(self::getScheduledEvents($task)) === 1;
    }

    /**
     * @internal
     *
     * @return array<array-key, Event>
     */
    private static function getScheduledEvents(string $task): array {
        $schedule = Container::getInstance()->make(Schedule::class);
        $events   = array_filter($schedule->events(), static function (Event $event) use ($task): bool {
            // Description?
            if (isset($event->description)) {
                return $event->description === $task;
            }

            // Command?
            if (isset($event->command)) {
                return $event->command === Application::formatCommandString($task);
            }

            // Callback?
            if ($event instanceof CallbackEvent) {
                return $event->getSummaryForDisplay() === $task;
            }

            // Nope
            return false;
        });

        return $events;
    }
}
