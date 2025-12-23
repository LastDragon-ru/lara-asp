<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Internals;

use function memory_get_peak_usage;
use function memory_get_usage;

/**
 * @internal
 */
readonly class Memory {
    public int $peak;
    public int $current;

    public function __construct() {
        $this->current = $this->current();
        $this->peak    = $this->peak();
    }

    protected function current(): int {
        return memory_get_usage(true);
    }

    protected function peak(): int {
        return memory_get_peak_usage(true);
    }
}
