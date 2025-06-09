<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions\Application;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Foundation\Application;
use LastDragon_ru\LaraASP\Testing\Assertions\Application\ScheduleMatchers\CallbackEventMatcher;
use LastDragon_ru\LaraASP\Testing\Assertions\Application\ScheduleMatchers\CommandMatcher;
use LastDragon_ru\LaraASP\Testing\Assertions\Application\ScheduleMatchers\DescriptionMatcher;
use PHPUnit\Framework\Assert;

use function array_filter;
use function count;
use function sprintf;

trait ScheduleAssertions {
    // <editor-fold desc="Abstract">
    // =========================================================================
    abstract protected function app(): Application;
    // </editor-fold>

    // <editor-fold desc="Assertions">
    // =========================================================================
    /**
     * Asserts that Schedule contains task.
     */
    public function assertScheduled(string $expected, ?string $message = null): void {
        $message ??= sprintf('The `%s` is not scheduled.', $expected);
        $scheduled = $this->isScheduledEvent($expected);

        Assert::assertTrue($scheduled, $message);
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    private function isScheduledEvent(string $task): bool {
        return count($this->getScheduledEvents($task)) === 1;
    }

    /**
     * @return array<array-key, Event>
     */
    private function getScheduledEvents(string $task): array {
        $container = $this->app();
        $schedule  = $container->make(Schedule::class);
        $matchers  = [
            new DescriptionMatcher(),
            new CommandMatcher($container),
            new CallbackEventMatcher(),
        ];
        $events    = array_filter($schedule->events(), static function (Event $event) use ($matchers, $task): bool {
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
    //</editor-fold>
}
