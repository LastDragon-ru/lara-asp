<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Events;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Event;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Hook;
use LastDragon_ru\Path\FilePath;

readonly class HookStarted implements Event {
    public function __construct(
        public Hook $hook,
        public FilePath $path,
    ) {
        // empty
    }
}
