<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Events;

use LastDragon_ru\LaraASP\Core\Path\FilePath;

readonly class FileStarted implements Event {
    public function __construct(
        public FilePath $path,
    ) {
        // empty
    }
}
