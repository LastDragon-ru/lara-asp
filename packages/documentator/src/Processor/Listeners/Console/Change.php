<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console;

readonly class Change {
    public function __construct(
        public string $path,
    ) {
        // empty
    }
}
