<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Data;

/**
 * @internal
 */
readonly class Coordinate {
    public function __construct(
        public int $line,
        public int $offset,
        public ?int $length,
    ) {
        // empty
    }
}
