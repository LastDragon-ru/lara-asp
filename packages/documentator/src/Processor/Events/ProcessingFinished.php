<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Events;

readonly class ProcessingFinished implements Event {
    public function __construct(
        public ProcessingFinishedResult $result,
    ) {
        // empty
    }
}
