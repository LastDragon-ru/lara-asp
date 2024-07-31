<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Nodes;

use IteratorAggregate;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Coordinate;
use Override;
use Traversable;

/**
 * @internal
 * @implements IteratorAggregate<array-key, Coordinate>
 */
readonly class Locator implements IteratorAggregate {
    public function __construct(
        private int $startLine,
        private int $endLine,
        private int $offset,
        private ?int $length = null,
        private int $padding = 0,
    ) {
        // empty
    }

    /**
     * @return Traversable<array-key, Coordinate>
     */
    #[Override]
    public function getIterator(): Traversable {
        if ($this->startLine === $this->endLine) {
            yield new Coordinate($this->startLine, $this->padding + $this->offset, $this->length);
        } else {
            for ($line = $this->startLine; $line <= $this->endLine; $line++) {
                yield match (true) {
                    $line === $this->startLine => new Coordinate($line, $this->padding + $this->offset, null),
                    $line === $this->endLine   => new Coordinate($line, $this->padding, $this->length),
                    default                    => new Coordinate($line, $this->padding, null),
                };
            }
        }

        yield from [];
    }
}
