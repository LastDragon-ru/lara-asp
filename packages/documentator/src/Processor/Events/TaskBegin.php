<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Events;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Event;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;

readonly class TaskBegin implements Event {
    public function __construct(
        /**
         * @var class-string<Task>
         */
        public string $task,
    ) {
        // empty
    }
}
