<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Editor\Locations;

use IteratorAggregate;
use LastDragon_ru\LaraASP\Documentator\Editor\Coordinate;
use Override;
use Traversable;

use function max;

/**
 * @implements IteratorAggregate<mixed, Coordinate>
 */
readonly class Location implements IteratorAggregate {
    public function __construct(
        public int $startLine,
        public int $endLine,
        public int $offset = 0,
        public ?int $length = null,
        public int $startLinePadding = 0,
        public ?int $internalPadding = null,
    ) {
        // empty
    }

    /**
     * @return Traversable<mixed, Coordinate>
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

    /**
     * @return new<self>
     */
    public function moveOffset(int $move): self {
        if ($move === 0) {
            return $this;
        }

        $offset = max(0, $this->offset + $move);

        return new self(
            $this->startLine,
            $this->endLine,
            $offset,
            $this->length !== null && $this->startLine === $this->endLine
                ? $this->length - ($offset - $this->offset)
                : $this->length,
            $this->startLinePadding,
            $this->internalPadding,
        );
    }

    /**
     * @return new<self>
     */
    public function withOffset(int $offset): self {
        return new self(
            $this->startLine,
            $this->endLine,
            $offset,
            $this->length,
            $this->startLinePadding,
            $this->internalPadding,
        );
    }

    /**
     * @return new<self>
     */
    public function withLength(?int $length): self {
        return new self(
            $this->startLine,
            $this->endLine,
            $this->offset,
            $length,
            $this->startLinePadding,
            $this->internalPadding,
        );
    }
}
