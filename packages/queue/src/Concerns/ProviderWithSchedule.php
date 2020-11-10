<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Concerns;

use LastDragon_ru\LaraASP\Queue\CronableRegistrator;

/**
 * @mixin \Illuminate\Support\ServiceProvider
 */
trait ProviderWithSchedule {
    /**
     * Define the command schedule.
     *
     * @param string[] $schedule {@link \LastDragon_ru\LaraASP\Queue\Contracts\Cronable} classes
     *
     * @return void
     */
    protected function bootSchedule(array $schedule): void {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->app->booted(function () use ($schedule) {
            $registrator = $this->app->make(CronableRegistrator::class);

            foreach ($schedule as $job) {
                $registrator->register($job);
            }
        });
    }
}
