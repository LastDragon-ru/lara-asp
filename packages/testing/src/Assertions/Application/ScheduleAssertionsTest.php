<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions\Application;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\ShouldQueue;
use LastDragon_ru\LaraASP\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(ScheduleAssertions::class)]
final class ScheduleAssertionsTest extends TestCase {
    public function testGetScheduleEvents(): void {
        $schedule        = Container::getInstance()->make(Schedule::class);
        $assertions      = new class() {
            use ScheduleAssertions {
                isScheduledEvent as public;
            }
        };
        $taskCommand     = 'test:command abc';
        $taskInvoke      = new class() {
            public function __invoke(): void {
                // empty
            }
        };
        $taskShouldQueue = new class() implements ShouldQueue {
            // empty
        };

        $schedule->command($taskCommand, ['--a' => 123])->daily();
        $schedule->job($taskShouldQueue)->monthly();
        $schedule->call($taskInvoke::class)->weekly();

        self::assertTrue($assertions::isScheduledEvent("{$taskCommand} --a=123"));
        self::assertTrue($assertions::isScheduledEvent($taskInvoke::class));
        self::assertTrue($assertions::isScheduledEvent($taskShouldQueue::class));
    }
}
