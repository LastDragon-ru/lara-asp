<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Location;

use IteratorAggregate;

/**
 * @extends IteratorAggregate<array-key, Coordinate>
 */
interface Location extends IteratorAggregate {
    public function getPadding(): int;
}
