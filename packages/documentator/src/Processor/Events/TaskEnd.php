<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Events;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Event;

readonly class TaskEnd implements Event {
    public function __construct(
        public TaskResult $result,
    ) {
        // empty
    }
}
