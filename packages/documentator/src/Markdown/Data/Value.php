<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Data;

/**
 * @template TValue
 * @internal
 */
interface Value {
    /**
     * @return TValue
     */
    public function get(): mixed;
}
