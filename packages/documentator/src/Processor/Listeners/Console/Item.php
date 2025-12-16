<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console;

use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskResult;

class Item {
    public function __construct(
        public string $title,
        public ?float $time,
        public TaskResult|DependencyResult $result,
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
