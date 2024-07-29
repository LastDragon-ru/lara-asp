<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Nodes;

/**
 * @internal
 */
class Line {
    public function __construct(
        public readonly int $number,
        public readonly int $offset,
        public readonly ?int $length,
    ) {
        // empty
    }
}
