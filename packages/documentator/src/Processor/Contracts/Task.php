<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\FileTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\HookTask;

/**
 * @phpstan-sealed FileTask|HookTask
 *
 * @see FileTask
 * @see HookTask
 */
interface Task {
    // empty
}
