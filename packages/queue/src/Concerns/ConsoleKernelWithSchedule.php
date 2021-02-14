<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Concerns;

use Illuminate\Console\Scheduling\Schedule;
use LastDragon_ru\LaraASP\Queue\CronableRegistrator;
use LogicException;

/**
 * @property string[] $schedule {@link \LastDragon_ru\LaraASP\Queue\Contracts\Cronable} classes
 * @mixin \Illuminate\Foundation\Console\Kernel
 */
trait ConsoleKernelWithSchedule {
    protected function schedule(Schedule $schedule): void {
        if (!isset($this->schedule)) {
            throw new LogicException('Class does not have $schedule property, please add it.');
        }

        $this->app->booted(function () use ($schedule): void {
            $registrator = $this->app->make(CronableRegistrator::class, [
                'schedule' => $schedule,
            ]);

            foreach ((array) $this->schedule as $job) {
                $registrator->register($job);
            }
        });
    }
}
