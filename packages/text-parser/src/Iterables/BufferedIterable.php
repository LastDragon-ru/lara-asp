<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Iterables;

use ArrayAccess;
use ArrayIterator;
use Iterator;
use IteratorIterator;
use LastDragon_ru\TextParser\Exceptions\OffsetOutOfBounds;
use LastDragon_ru\TextParser\Exceptions\OffsetReadonly;
use Override;
use SeekableIterator;
use SplDoublyLinkedList;
use Traversable;

/**
 * Buffers the specified number of items and allows to seek within this range.
 *
 * ```
 * [----- previous -----|----- next -----]
 *                    cursor
 * ```
 *
 * @template TValue
 *
 * @implements Iterator<int, TValue>
 * @implements ArrayAccess<int, TValue>
 * @implements SeekableIterator<int, TValue>
 */
class BufferedIterable implements Iterator, SeekableIterator, ArrayAccess {
    /**
     * @var SplDoublyLinkedList<TValue>
     */
    protected SplDoublyLinkedList $buffer;
    /**
     * @var Iterator<mixed, TValue>
     */
    protected Iterator $iterator;
    protected int      $cursor;
    protected int      $key;

    public function __construct(
        /**
         * @var iterable<mixed, TValue>
         */
        protected readonly iterable $iterable,
        /**
         * @var positive-int
         */
        protected readonly int $previous,
        /**
         * @var positive-int
         */
        protected readonly int $next,
    ) {
        $this->key      = 0;
        $this->cursor   = 0;
        $this->buffer   = new SplDoublyLinkedList();
        $this->iterator = match (true) {
            $this->iterable instanceof Iterator    => $this->iterable,
            $this->iterable instanceof Traversable => new IteratorIterator($this->iterable),
            default                                => new ArrayIterator($this->iterable),
        };
    }

    #[Override]
    public function key(): mixed {
        return $this->key;
    }

    #[Override]
    public function current(): mixed {
        return $this->buffer[$this->cursor];
    }

    #[Override]
    public function next(): void {
        $this->cursor++;
        $this->key++;

        $this->cleanup();
        $this->fill();
    }

    #[Override]
    public function valid(): bool {
        return $this->cursor < $this->buffer->count();
    }

    #[Override]
    public function rewind(): void {
        $this->key    = 0;
        $this->cursor = 0;
        $this->buffer = new SplDoublyLinkedList();

        $this->iterator->rewind();

        $this->fill();
    }

    #[Override]
    public function seek(int $offset): void {
        $diff   = $offset - $this->key;
        $cursor = $this->cursor + $diff;

        if (!isset($this->buffer[$cursor])) {
            throw new OffsetOutOfBounds($offset);
        }

        $this->key    = $this->key + $diff;
        $this->cursor = $cursor;

        $this->cleanup();
        $this->fill();
    }

    #[Override]
    public function offsetExists(mixed $offset): bool {
        return isset($this->buffer[$this->cursor + $offset]);
    }

    #[Override]
    public function offsetGet(mixed $offset): mixed {
        if (!$this->offsetExists($offset)) {
            throw new OffsetOutOfBounds($offset);
        }

        return $this->buffer[$this->cursor + $offset];
    }

    #[Override]
    public function offsetSet(mixed $offset, mixed $value): void {
        throw new OffsetReadonly($offset);
    }

    #[Override]
    public function offsetUnset(mixed $offset): void {
        throw new OffsetReadonly($offset);
    }

    private function fill(): void {
        for (
            $count = $this->next - ($this->buffer->count() - $this->cursor);
            $this->iterator->valid() && $count >= 0;
            $this->iterator->next(), $count--
        ) {
            $this->buffer->push($this->iterator->current());
        }
    }

    private function cleanup(): void {
        while ($this->cursor > $this->previous) {
            $this->buffer->shift();
            $this->cursor--;
        }
    }
}
