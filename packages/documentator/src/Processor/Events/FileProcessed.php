<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Events;

readonly class FileProcessed implements Event {
    public function __construct(
        public FileProcessedResult $result,
    ) {
        // empty
    }
}
