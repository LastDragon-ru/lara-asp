<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Events;

use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;

readonly class DependencyResolved implements Event {
    public function __construct(
        public DirectoryPath|FilePath $path,
        public DependencyResolvedResult $result,
    ) {
        // empty
    }
}
