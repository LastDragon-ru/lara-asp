<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Foundation\Application;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Queue\Configs\CronableConfig;
use LastDragon_ru\LaraASP\Queue\Contracts\Cronable;
use LastDragon_ru\LaraASP\Queue\Testing\Package\TestCase;
use LogicException;
use Mockery;
use Mockery\MockInterface;

use function sprintf;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Queue\CronableRegistrator
 */
class CronableRegistratorTest extends TestCase {
    /**
     * @covers ::register
     */
    public function testRegister(): void {
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
                ->shouldReceive('job')
                ->once()
                ->andReturnSelf();
            $schedule
                ->shouldReceive('cron')
                ->with('* * * * *')
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
            CronableConfig::Cron    => '* * * * *',
            CronableConfig::Enabled => true,
        ]);

        $registrator = $this->app->make(CronableRegistrator::class);

        $registrator->register($cronable::class);
    }

    /**
     * @covers ::register
     */
    public function testRegisterNotCronable(): void {
        $registrator = new class() extends CronableRegistrator {
            /** @noinspection PhpMissingParentConstructorInspection */
            public function __construct() {
                // empty
            }
        };

        $this->expectExceptionObject(new InvalidArgumentException(
            sprintf('The `$cronable` must implement %s.', Cronable::class),
        ));

        $registrator->register($this::class);
    }

    /**
     * @covers ::register
     */
    public function testRegisterNotConsole(): void {
        $cronable    = Mockery::mock(Cronable::class)::class;
        $application = Mockery::mock(Application::class);
        $application
            ->shouldReceive('runningInConsole')
            ->once()
            ->andReturn(false);

        $registrator = new class($application) extends CronableRegistrator {
            /** @noinspection PhpMissingParentConstructorInspection */
            public function __construct(
                protected Application $application,
            ) {
                // empty
            }
        };

        $this->expectExceptionObject(new LogicException('The application is not running in console.'));

        $registrator->register($cronable);
    }

    /**
     * @covers ::register
     */
    public function testRegisterNoSettingCron(): void {
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
            CronableConfig::Enabled => true,
        ]);

        $registrator = $this->app->make(CronableRegistrator::class);

        $registrator->register($cronable::class);
    }

    /**
     * @covers ::register
     */
    public function testRegisterDisabled(): void {
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
            CronableConfig::Cron    => '* * * * *',
            CronableConfig::Enabled => false,
        ]);

        $registrator = $this->app->make(CronableRegistrator::class);

        $registrator->register($cronable::class);
    }
}
