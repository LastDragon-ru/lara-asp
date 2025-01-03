<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console;

class Task {
    public function __construct(
        public readonly string $title,
        public readonly float $start,
        public float $paused = 0,
        /**
         * @var list<Item>
         */
        public array $children = [],
    ) {
        // empty
    }
}
