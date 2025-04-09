<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Editor\Locations;

use InvalidArgumentException;
use IteratorAggregate;
use LastDragon_ru\LaraASP\Documentator\Editor\Coordinate;
use LastDragon_ru\LaraASP\Documentator\Utils\Integer;
use Override;
use Traversable;

use function max;

use const PHP_INT_MAX;

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
                Integer::add($this->startLinePadding, $this->offset),
                $this->length,
                $this->startLinePadding,
            );
        } else {
            for ($line = $this->startLine; $line <= $this->endLine; $line++) {
                yield match (true) {
                    $line === $this->startLine => new Coordinate(
                        $line,
                        Integer::add($this->startLinePadding, $this->offset),
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
    public function before(): self {
        return new self(
            $this->startLine,
            $this->startLine,
            $this->offset,
            0,
            $this->startLinePadding,
            $this->internalPadding,
        );
    }

    /**
     * @return new<self>
     */
    public function after(): self {
        return new self(
            $this->endLine,
            $this->endLine,
            $this->length !== null
                ? Integer::add(($this->startLine === $this->endLine ? $this->offset : 0), $this->length)
                : PHP_INT_MAX,
            0,
            $this->internalPadding ?? $this->startLinePadding,
        );
    }

    /**
     * @return new<self>
     */
    public function move(int $move): self {
        $lines     = $this->endLine - $this->startLine;
        $startLine = max(0, $this->startLine + $move);

        return new self(
            $startLine,
            $startLine + $lines,
            $this->offset,
            $this->length,
            $this->startLinePadding,
            $this->internalPadding,
        );
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
    public function moveLength(int $move): self {
        if ($move === 0 || $this->length === null) {
            return $this;
        }

        $length = max(0, $this->length + $move);

        return new self(
            $this->startLine,
            $this->endLine,
            $this->offset,
            $length,
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

    /**
     * @return new<self>
     */
    public function withStartLine(int $startLine): self {
        if ($startLine === $this->startLine) {
            return $this;
        }

        if ($startLine > $this->endLine) {
            throw new InvalidArgumentException(
                'The `$startLine` must be lower than or equal to `Location::$endLine`.',
            );
        }

        $startLinePadding = $this->internalPadding ?? $this->startLinePadding;

        return new self(
            $startLine,
            $this->endLine,
            $this->offset,
            $this->length,
            $startLinePadding,
            $this->internalPadding,
        );
    }

    /**
     * @return new<self>
     */
    public function withEndLine(int $endLine): self {
        if ($endLine === $this->endLine) {
            return $this;
        }

        if ($endLine < $this->startLine) {
            throw new InvalidArgumentException(
                'The `$endLine` must be greater than or equal to `Location::$startLine`.',
            );
        }

        $internalPadding = $endLine > $this->startLine ? $this->internalPadding : null;

        return new self(
            $this->startLine,
            $endLine,
            $this->offset,
            $this->length,
            $this->startLinePadding,
            $internalPadding,
        );
    }
}
