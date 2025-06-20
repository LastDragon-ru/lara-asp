<?php declare(strict_types = 1);

namespace LastDragon_ru\DiyParser\Streams;

use ArrayAccess;
use Iterator;
use OutOfBoundsException;
use Override;

use function array_pop;

/**
 * Provides "transaction" support for the stream.
 *
 * @template TValue
 *
 * @implements Iterator<int, TValue>
 * @implements ArrayAccess<int, TValue>
 */
class TransactionalStream implements Iterator, ArrayAccess {
    /**
     * @var BufferedStream<TValue>
     */
    private BufferedStream $source;

    /**
     * @var list<int>
     */
    private array $stack = [];
    private ?int  $eos   = null;

    public function __construct(
        /**
         * @var iterable<mixed, TValue>
         */
        protected readonly iterable $stream,
        /**
         * @var positive-int
         */
        protected readonly int $previous,
        /**
         * @var positive-int
         */
        protected readonly int $next,
    ) {
        $this->source = new BufferedStream($this->stream, 2 * $this->previous, $this->next);

        $this->source->rewind();
    }

    public function begin(): void {
        $offset        = $this->source->key();
        $this->eos   ??= $offset + $this->previous;
        $this->stack[] = $offset;
    }

    public function end(mixed $result): void {
        if ($result === null || $result === false) {
            $this->rollback();
        } else {
            $this->commit();
        }
    }

    public function commit(): void {
        array_pop($this->stack);

        if ($this->stack === []) {
            $this->eos = null;
        }
    }

    public function rollback(): void {
        $offset = array_pop($this->stack);

        if ($offset !== null) {
            $this->source->seek($offset);
        }

        if ($this->stack === []) {
            $this->eos = null;
        }
    }

    #[Override]
    public function offsetExists(mixed $offset): bool {
        return isset($this->source[$offset]);
    }

    #[Override]
    public function offsetGet(mixed $offset): mixed {
        if (($offset > 0 && $offset > $this->next) || ($offset < 0 && -$offset > $this->previous)) {
            throw new OutOfBoundsException('The `$offset` is out of bounds.');
        }

        return $this->source[$offset] ?? null;
    }

    #[Override]
    public function offsetSet(mixed $offset, mixed $value): void {
        $this->source[$offset] = $value;
    }

    #[Override]
    public function offsetUnset(mixed $offset): void {
        unset($this->source[$offset]);
    }

    #[Override]
    public function valid(): bool {
        return $this->source->valid()
            && ($this->eos === null || $this->source->key() < $this->eos);
    }

    #[Override]
    public function key(): mixed {
        return $this->source->key();
    }

    #[Override]
    public function current(): mixed {
        return $this->source->current();
    }

    #[Override]
    public function next(int $count = 1): void {
        for (; $this->source->valid() && $count > 0; $count--) {
            $this->source->next();
        }
    }

    #[Override]
    public function rewind(): void {
        // Not supported.
    }
}
