<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Iterables;

use ArrayAccess;
use Iterator;
use LastDragon_ru\TextParser\Exceptions\OffsetOutOfBounds;
use Override;

use function array_pop;
use function count;

/**
 * Provides "transaction" support for the iterable.
 *
 * @property-read int<0, max> $level
 * @property-read mixed       $name
 *
 * @template TValue
 *
 * @implements Iterator<int, TValue>
 * @implements ArrayAccess<int, TValue>
 */
class TransactionalIterable implements Iterator, ArrayAccess {
    /**
     * @var BufferedIterable<TValue>
     */
    private BufferedIterable $source;

    /**
     * @var list<array{int, mixed}>
     */
    private array $stack = [];
    private ?int  $eos   = null;

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
        $this->source = new BufferedIterable($this->iterable, 2 * $this->previous, $this->next);

        $this->source->rewind();
    }

    public function begin(mixed $name = null): void {
        $offset        = $this->source->key();
        $this->eos   ??= $offset + $this->previous;
        $this->stack[] = [$offset, $name];
    }

    public function end(mixed $result, mixed $name = null): bool {
        $commited = !($result === null || $result === false)
            && ($name === null || $name === ($this->stack[count($this->stack) - 1][1] ?? null));

        if ($commited) {
            $this->commit();
        } else {
            $this->rollback();
        }

        return $commited;
    }

    public function commit(): void {
        array_pop($this->stack);

        if ($this->stack === []) {
            $this->eos = null;
        }
    }

    public function rollback(): void {
        [$offset] = (array) array_pop($this->stack) + [null];

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
            throw new OffsetOutOfBounds($offset);
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

    /**
     * @deprecated %{VERSION} Will be replaced to property hooks soon.
     */
    public function __get(mixed $name): mixed {
        return match ($name) {
            'level' => count($this->stack),
            'name'  => $this->stack[count($this->stack) - 1][1] ?? null,
            default => null,
        };
    }

    public function isInside(mixed $name): bool {
        $inside = false;

        for ($i = count($this->stack) - 2; $i >= 0; $i--) {
            if ($this->stack[$i][1] === $name) {
                $inside = true;
                break;
            }
        }

        return $inside;
    }
}
