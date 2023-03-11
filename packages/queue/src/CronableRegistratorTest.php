<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue;

use Closure;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Queue;
use LastDragon_ru\LaraASP\Queue\Configs\CronableConfig;
use LastDragon_ru\LaraASP\Queue\Contracts\Cronable;
use LastDragon_ru\LaraASP\Queue\Testing\Package\TestCase;
use LogicException;
use Mockery;
use Mockery\MockInterface;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\Queue\CronableRegistrator
 */
class CronableRegistratorTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderRegister
     */
    public function testRegister(bool $enabled): void {
        // Mocks
        $cronable = new class() implements Cronable {
            /**
             * @inheritDoc
             */
            public function getQueueConfig(): array {
                return [];
            }
        };

        $this->override(Schedule::class, static function (MockInterface $schedule): void {
            $schedule
                ->shouldReceive('call')
                ->once()
                ->andReturnUsing(static function (Closure $callback) use ($schedule): mixed {
                    self::assertTrue($callback());

                    return $schedule;
                });
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
                ->shouldReceive('description')
                ->once()
                ->andReturnSelf();
            $schedule
                ->shouldReceive('after')
                ->once()
                ->andReturnSelf();
        });

        $this->setQueueableConfig($cronable, [
            CronableConfig::Cron     => '* * * * *',
            CronableConfig::Enabled  => $enabled,
            CronableConfig::Timezone => 'Europe/Moscow',
        ]);

        Queue::fake();

        $schedule    = $this->app->make(Schedule::class);
        $registrator = $this->app->make(CronableRegistrator::class);

        $registrator->register($this->app, $schedule, $cronable::class);

        if ($enabled) {
            Queue::assertPushed($cronable::class);
        } else {
            Queue::assertNothingPushed();
        }
    }

    public function testRegisterNotConsole(): void {
        $cronable = new class() implements Cronable {
            /**
             * @inheritDoc
             */
            public function getQueueConfig(): array {
                return [];
            }
        };
        $app      = Mockery::mock(Application::class);
        $app
            ->shouldReceive('runningInConsole')
            ->once()
            ->andReturn(false);

        $schedule    = $this->app->make(Schedule::class);
        $registrator = new class() extends CronableRegistrator {
            /** @noinspection PhpMissingParentConstructorInspection */
            public function __construct() {
                // empty
            }
        };

        self::expectExceptionObject(new LogicException('The application is not running in console.'));

        $registrator->register($app, $schedule, $cronable::class);
    }

    public function testRegisterNoCron(): void {
        $cronable = new class() implements Cronable {
            /**
             * @inheritDoc
             */
            public function getQueueConfig(): array {
                return [];
            }
        };

        $this->override(Schedule::class);
        $this->setQueueableConfig($cronable, [
            CronableConfig::Cron    => null,
            CronableConfig::Enabled => false,
        ]);

        $schedule    = $this->app->make(Schedule::class);
        $registrator = $this->app->make(CronableRegistrator::class);

        $registrator->register($this->app, $schedule, $cronable::class);
    }

    public function testRegisterCronIsNull(): void {
        $cronable = new class() implements Cronable {
            /**
             * @inheritDoc
             */
            public function getQueueConfig(): array {
                return [];
            }
        };

        $this->override(Schedule::class, static function (MockInterface $mock): void {
            $mock
                ->shouldReceive('call')
                ->never();
        });

        $this->setQueueableConfig($cronable, [
            CronableConfig::Cron    => null,
            CronableConfig::Enabled => true,
        ]);

        $schedule    = $this->app->make(Schedule::class);
        $registrator = $this->app->make(CronableRegistrator::class);

        $registrator->register($this->app, $schedule, $cronable::class);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{bool}>
     */
    public static function dataProviderRegister(): array {
        return [
            'enabled'  => [true],
            'disabled' => [false],
        ];
    }
    // </editor-fold>
}
