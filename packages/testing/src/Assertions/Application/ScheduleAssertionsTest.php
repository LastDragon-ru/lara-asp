<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions\Application;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Queue\ShouldQueue;
use LastDragon_ru\LaraASP\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * @internal
 */
#[CoversClass(ScheduleAssertions::class)]
final class ScheduleAssertionsTest extends TestCase {
    public function testGetScheduleEvents(): void {
        $schedule         = $this->app()->make(Schedule::class);
        $assertions       = new class() {
            use ScheduleAssertions {
                isScheduledEvent as public;
            }
        };
        $taskExec         = '/path/to/command';
        $taskCommand      = 'test:command abc';
        $taskCommandClass = ScheduleAssertionsTest__Command::class;
        $taskInvoke       = new class() {
            public function __invoke(): void {
                // empty
            }
        };
        $taskShouldQueue  = new class() implements ShouldQueue {
            // empty
        };

        $schedule->command($taskCommand, ['--a' => 123])->daily();
        $schedule->command($taskCommandClass)->daily();
        $schedule->job($taskShouldQueue)->monthly();
        $schedule->call($taskInvoke::class)->weekly();
        $schedule->exec($taskExec)->weekly();

        self::assertTrue($assertions::isScheduledEvent("{$taskCommand} --a=123"));
        self::assertTrue($assertions::isScheduledEvent($taskCommandClass));
        self::assertTrue($assertions::isScheduledEvent($taskInvoke::class));
        self::assertTrue($assertions::isScheduledEvent($taskShouldQueue::class));
        self::assertTrue($assertions::isScheduledEvent($taskExec));
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
#[AsCommand(
    name       : 'test',
    description: 'Test command.',
)]
class ScheduleAssertionsTest__Command extends Command {
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    protected $signature = 'test {file : example file}';

    public function __invoke(): int {
        return self::SUCCESS;
    }
}
