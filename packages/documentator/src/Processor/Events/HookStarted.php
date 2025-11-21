<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Events;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Hook;

readonly class HookStarted implements Event {
    public function __construct(
        public Hook $hook,
        public FilePath $path,
    ) {
        // empty
    }
}
