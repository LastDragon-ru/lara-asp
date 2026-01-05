<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Events;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Event;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;

readonly class Dependency implements Event {
    public function __construct(
        public DirectoryPath|FilePath $path,
        public DependencyResult $result,
    ) {
        // empty
    }
}
