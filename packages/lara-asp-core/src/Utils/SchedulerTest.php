<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Utils;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Queue\ShouldQueue;
use LastDragon_ru\LaraASP\Core\Contracts\Schedulable;
use LastDragon_ru\LaraASP\Core\Package\TestCase;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Scheduler::class)]
final class SchedulerTest extends TestCase {
    public function testRegisterClass(): void {
        // Mocks
        $job = new class() implements Schedulable {
            /**
             * @inheritDoc
             */
            #[Override]
            public function getSchedule(): array {
                return [
                    'cron'               => '* * * * *',
                    'enabled'            => true,
                    'timezone'           => 'Europe/Moscow',
                    'inMaintenanceMode'  => true,
                    'withoutOverlapping' => 123,
                ];
            }

            public function __invoke(): void {
                // empty
            }
        };

        $this->override(Schedule::class, static function (MockInterface $schedule) use ($job): void {
            $schedule
                ->shouldReceive('call')
                ->with(Mockery::type($job::class))
                ->once()
                ->andReturnSelf();
            $schedule
                ->shouldReceive('cron')
                ->with('* * * * *')
                ->once()
                ->andReturnSelf();
            $schedule
                ->shouldReceive('timezone')
                ->with('Europe/Moscow')
                ->once()
                ->andReturnSelf();
            $schedule
                ->shouldReceive('withoutOverlapping')
                ->with(123)
                ->once()
                ->andReturnSelf();
            $schedule
                ->shouldReceive('evenInMaintenanceMode')
                ->once()
                ->andReturnSelf();
        });

        $schedule  = $this->app()->make(Schedule::class);
        $scheduler = $this->app()->make(Scheduler::class);

        self::assertTrue(
            $scheduler->register($schedule, $job::class),
        );
    }

    public function testRegisterShouldQueue(): void {
        // Mocks
        $job = new class() implements ShouldQueue, Schedulable {
            /**
             * @inheritDoc
             */
            #[Override]
            public function getSchedule(): array {
                return [
                    'cron'    => '* * * * *',
                    'enabled' => true,
                ];
            }
        };

        $this->override(Schedule::class, static function (MockInterface $schedule) use ($job): void {
            $schedule
                ->shouldReceive('job')
                ->with(Mockery::type($job::class))
                ->once()
                ->andReturnSelf();
            $schedule
                ->shouldReceive('cron')
                ->with('* * * * *')
                ->once()
                ->andReturnSelf();
            $schedule
                ->shouldReceive('timezone')
                ->never();
            $schedule
                ->shouldReceive('withoutOverlapping')
                ->never();
            $schedule
                ->shouldReceive('evenInMaintenanceMode')
                ->never();
        });

        $schedule  = $this->app()->make(Schedule::class);
        $scheduler = $this->app()->make(Scheduler::class);

        self::assertTrue(
            $scheduler->register($schedule, $job::class),
        );
    }

    public function testRegisterEmptyCron(): void {
        $job = new class() implements Schedulable {
            /**
             * @inheritDoc
             */
            #[Override]
            public function getSchedule(): array {
                return [
                    'cron'    => '',
                    'enabled' => true,
                ];
            }
        };

        $schedule  = Mockery::mock(Schedule::class);
        $scheduler = $this->app()->make(Scheduler::class);

        self::assertFalse(
            $scheduler->register($schedule, $job::class),
        );
    }

    public function testRegisterDisabled(): void {
        $job = new class() implements Schedulable {
            /**
             * @inheritDoc
             */
            #[Override]
            public function getSchedule(): array {
                return [
                    'cron'    => '* * * * *',
                    'enabled' => false,
                ];
            }
        };

        $schedule  = Mockery::mock(Schedule::class);
        $scheduler = $this->app()->make(Scheduler::class);

        self::assertFalse(
            $scheduler->register($schedule, $job::class),
        );
    }

    public function testRegisterDefaultSettings(): void {
        $job = new class() {
            // empty
        };

        $schedule  = Mockery::mock(Schedule::class);
        $scheduler = $this->app()->make(Scheduler::class);

        self::assertFalse(
            $scheduler->register($schedule, $job::class),
        );
    }
}
