<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Utils;

/**
 * @internal
 */
readonly class UnicodeDateTimeFormatToken {
    public function __construct(
        public string $pattern,
        public string $value,
    ) {
        // empty
    }
}
