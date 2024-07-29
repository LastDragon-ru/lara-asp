<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Nodes;

/**
 * @internal
 */
interface Locationable {
    /**
     * @return iterable<array-key, Line>
     */
    public function getLocation(): iterable;
}
