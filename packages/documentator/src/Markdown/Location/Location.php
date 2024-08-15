<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Location;

use IteratorAggregate;
use Override;
use Traversable;

/**
 * @implements IteratorAggregate<array-key, Coordinate>
 */
readonly class Location implements IteratorAggregate {
    public function __construct(
        private int $startLine,
        private int $endLine,
        private int $offset = 0,
        private ?int $length = null,
        private int $startLinePadding = 0,
        private ?int $internalPadding = null,
    ) {
        // empty
    }

    /**
     * @return Traversable<array-key, Coordinate>
     */
    #[Override]
    public function getIterator(): Traversable {
        if ($this->startLine === $this->endLine) {
            yield new Coordinate(
                $this->startLine,
                $this->startLinePadding + $this->offset,
                $this->length,
                $this->startLinePadding,
            );
        } else {
            for ($line = $this->startLine; $line <= $this->endLine; $line++) {
                yield match (true) {
                    $line === $this->startLine => new Coordinate(
                        $line,
                        $this->startLinePadding + $this->offset,
                        null,
                        $this->startLinePadding,
                    ),
                    $line === $this->endLine   => new Coordinate(
                        $line,
                        $this->internalPadding ?? $this->startLinePadding,
                        $this->length,
                        $this->internalPadding ?? $this->startLinePadding,
                    ),
                    default                    => new Coordinate(
                        $line,
                        $this->internalPadding ?? $this->startLinePadding,
                        null,
                        $this->internalPadding ?? $this->startLinePadding,
                    ),
                };
            }
        }

        yield from [];
    }
}
