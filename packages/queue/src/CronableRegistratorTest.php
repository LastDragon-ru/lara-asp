<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Date;
use LastDragon_ru\LaraASP\Queue\Configs\CronableConfig;
use LastDragon_ru\LaraASP\Queue\Contracts\Cronable;
use LastDragon_ru\LaraASP\Queue\Testing\Package\TestCase;
use LogicException;
use Mockery;
use Mockery\MockInterface;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Queue\CronableRegistrator
 */
class CronableRegistratorTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
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
                ->shouldReceive('call')
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
            CronableConfig::Enabled  => true,
            CronableConfig::Timezone => 'Europe/Moscow',
        ]);

        $registrator = $this->app->make(CronableRegistrator::class);

        $registrator->register($cronable::class);
    }

    /**
     * @covers ::register
     */
    public function testRegisterNotConsole(): void {
        $cronable    = new class() implements Cronable {
            /**
             * @inheritDoc
             */
            public function getQueueConfig(): array {
                return [];
            }
        };
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

    /**
     * @covers ::isDue
     *
     * @dataProvider dataProviderIsDue
     */
    public function testIsDue(bool $expected, string $date, ?string $cron): void {
        Date::setTestNow($date);

        $registrator = new class() extends CronableRegistrator {
            /** @noinspection PhpMissingParentConstructorInspection */
            public function __construct() {
                // empty
            }

            public function isDue(?string $cron): bool {
                return parent::isDue($cron);
            }
        };

        $this->assertEquals($expected, $registrator->isDue($cron));
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{bool, string, string|null}>
     */
    public function dataProviderIsDue(): array {
        return [
            'valid and due'     => [
                true,
                '2021-09-04T00:05:05.000+00:00',
                '5 * * * *',
            ],
            'valid and not due' => [
                false,
                '2021-09-04T00:05:05.000+00:00',
                '10 * * * *',
            ],
            'not valid'         => [
                false,
                '2021-09-04T00:05:05.000+00:00',
                '99 999 99 99 *',
            ],
            'empty'             => [
                false,
                '2021-09-04T00:05:05.000+00:00',
                '',
            ],
            'null'              => [
                false,
                '2021-09-04T00:05:05.000+00:00',
                null,
            ],
        ];
    }
    // </editor-fold>
}
