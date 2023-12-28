<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Utils;

/**
 * @internal
 */
class UnicodeDateTimeFormatToken {
    public function __construct(
        public readonly string $pattern,
        public readonly string $value,
    ) {
        // empty
    }
}
