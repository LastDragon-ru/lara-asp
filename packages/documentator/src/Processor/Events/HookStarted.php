<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Events;

use LastDragon_ru\LaraASP\Documentator\Processor\Hook;

readonly class HookStarted implements Event {
    public function __construct(
        public Hook $hook,
        /**
         * @var non-empty-string
         */
        public string $path,
    ) {
        // empty
    }
}
