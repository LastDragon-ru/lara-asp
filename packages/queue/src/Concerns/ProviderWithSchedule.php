<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Concerns;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Queue\Contracts\Cronable;
use LastDragon_ru\LaraASP\Queue\CronableRegistrator;

/**
 * @mixin ServiceProvider
 */
trait ProviderWithSchedule {
    /**
     * Define the command schedule.
     *
     * @param class-string<Cronable> ...$jobs
     */
    protected function bootSchedule(string ...$jobs): void {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->callAfterResolving(
            Schedule::class,
            static function (Schedule $schedule, Application $app) use ($jobs): void {
                $registrator = $app->make(CronableRegistrator::class);

                foreach ($jobs as $job) {
                    $registrator->register($app, $schedule, $job);
                }
            },
        );
    }
}
