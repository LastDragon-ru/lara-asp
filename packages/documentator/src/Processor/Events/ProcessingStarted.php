<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Events;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Event;
use LastDragon_ru\Path\DirectoryPath;

readonly class ProcessingStarted implements Event {
    public function __construct(
        public DirectoryPath $input,
        public DirectoryPath $output,
    ) {
        // empty
    }
}
