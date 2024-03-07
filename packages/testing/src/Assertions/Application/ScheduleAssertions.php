<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions\Application;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\Testing\Assertions\Application\ScheduleMatchers\CallbackEventMatcher;
use LastDragon_ru\LaraASP\Testing\Assertions\Application\ScheduleMatchers\CommandMatcher;
use LastDragon_ru\LaraASP\Testing\Assertions\Application\ScheduleMatchers\DescriptionMatcher;
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
     * @return array<array-key, Event>
     */
    private static function getScheduledEvents(string $task): array {
        $schedule = Container::getInstance()->make(Schedule::class);
        $matchers = [
            new DescriptionMatcher(),
            new CommandMatcher(),
            new CallbackEventMatcher(),
        ];
        $events   = array_filter($schedule->events(), static function (Event $event) use ($matchers, $task): bool {
            $match = false;

            foreach ($matchers as $matcher) {
                if ($matcher->isMatch($event, $task)) {
                    $match = true;
                    break;
                }
            }

            return $match;
        });

        return $events;
    }
}
