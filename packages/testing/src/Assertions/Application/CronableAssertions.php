<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions\Application;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use LastDragon_ru\LaraASP\Queue\Contracts\Cronable;
use function array_filter;
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
    protected function assertCronableRegistered(string $cronable, string $message = ''): void {
        $this->assertTrue(
            is_subclass_of($cronable, Cronable::class, true),
            sprintf('The "%s" must be instance of "%s".', $cronable, Cronable::class)
        );

        $schedule = $this->app->make(Schedule::class);
        $events   = array_filter($schedule->events(), function (Event $event) use ($cronable): bool {
            return str_contains($event->description ?? '', $cronable);
        });

        $this->assertEquals(1, count($events), $message ?: sprintf('The "%s" is not registered as scheduled job.', $cronable));
    }
}
