<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Concerns;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Container\Container;
use Illuminate\Foundation\Console\Kernel;
use LastDragon_ru\LaraASP\Queue\Contracts\Cronable;
use LastDragon_ru\LaraASP\Queue\CronableRegistrator;
use LogicException;
use Override;

/**
 * @property list<class-string<Cronable>> $schedule
 * @mixin Kernel
 */
trait ConsoleKernelWithSchedule {
    #[Override]
    protected function schedule(Schedule $schedule): void {
        if (!isset($this->schedule)) {
            throw new LogicException('Class does not have $schedule property, please add it.');
        }

        $registrator = Container::getInstance()->make(CronableRegistrator::class);

        foreach ($this->schedule as $job) {
            $registrator->register($this->app, $schedule, $job);
        }
    }
}
