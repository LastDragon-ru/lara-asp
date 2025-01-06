<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console;

class File {
    public function __construct(
        public readonly string $path,
        public readonly float $start,
        public float $paused = 0,
        /**
         * @var list<Change>
         */
        public array $changes = [],
        /**
         * @var list<Item>
         */
        public array $children = [],
    ) {
        // empty
    }
}
