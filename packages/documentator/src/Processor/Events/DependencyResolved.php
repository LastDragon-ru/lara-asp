<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Events;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;

readonly class DependencyResolved implements Event {
    public function __construct(
        /**
         * @var class-string<Dependency<*>>
         */
        public string $dependency,
        /**
         * @var non-empty-string
         */
        public string $path,
        public DependencyResolvedResult $result,
    ) {
        // empty
    }
}
