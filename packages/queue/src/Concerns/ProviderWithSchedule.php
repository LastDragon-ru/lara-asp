<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Concerns;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Container\Container;
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

        $this->app->afterResolving(
            Schedule::class,
            static function (Schedule $schedule, Container $container) use ($jobs): void {
                $registrator = $container->make(CronableRegistrator::class);

                foreach ($jobs as $job) {
                    $registrator->register($schedule, $job);
                }
            },
        );
    }
}
