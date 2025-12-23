<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Internals;

/**
 * @internal
 */
class Usage {
    public function __construct(
        public float $time,
        /**
         * @var positive-int
         */
        public int $count,
        /**
         * @var int<0, max>
         */
        public int $bytes,
    ) {
        // empty
    }
}
