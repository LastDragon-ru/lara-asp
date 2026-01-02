<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\FileTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\HookTask;

/**
 * @see HookTask
 */
enum Hook {
    /**
     * To run task before any other files.
     *
     * The {@see HookTask::__invoke($file)} is the file that will be processed
     * first.
     *
     * Calling the {@see Resolver::get()} will return the original
     * (= without processing by any of the tasks) dependency.
     */
    case Before;

    /**
     * To run task after all files.
     *
     * The {@see HookTask::__invoke($file)} is the file that was processed last.
     *
     * Calling the {@see Resolver::queue()} will throw an error
     * because it makes no sense.
     */
    case After;

    /**
     * To run task for each file.
     *
     * Unlike {@see FileTask} it doesn't search any files, just runs the task
     * for each file that was found by all other {@see FileTask}.
     *
     * @see FileTask
     */
    case File;
}
