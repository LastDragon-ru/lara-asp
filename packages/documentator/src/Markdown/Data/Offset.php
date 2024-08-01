<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Data;

use Override;

/**
 * @internal
 * @implements Value<int>
 */
readonly class Offset implements Value {
    public function __construct(
        private int $value,
    ) {
        // empty
    }

    #[Override]
    public function get(): mixed {
        return $this->value;
    }
}
