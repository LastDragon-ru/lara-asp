<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Contracts;

use LastDragon_ru\LaraASP\Core\Utils\Scheduler;

/**
 * @phpstan-import-type SchedulableSettings from Scheduler
 */
interface Schedulable {
    /**
     * @return SchedulableSettings
     */
    public function getSchedule(): array;
}
