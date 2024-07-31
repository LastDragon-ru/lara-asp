<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Data;

use IteratorAggregate;
use Override;
use Traversable;

/**
 * @internal
 * @implements IteratorAggregate<array-key, Coordinate>
 */
class Location implements IteratorAggregate {
    public function __construct(
        /**
         * @var iterable<array-key, Coordinate>
         */
        private readonly iterable $coordinates,
    ) {
        // empty
    }

    /**
     * @return Traversable<array-key, Coordinate>
     */
    #[Override]
    public function getIterator(): Traversable {
        yield from $this->coordinates;
    }
}
