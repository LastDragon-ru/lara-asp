<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console;

use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResolvedResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskFinishedResult;

readonly class Item {
    public function __construct(
        public string $title,
        public ?float $time,
        public TaskFinishedResult|DependencyResolvedResult $result,
        /**
         * @var list<Item>
         */
        public array $children = [],
    ) {
        // empty
    }
}
