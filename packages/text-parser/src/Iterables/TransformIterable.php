<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Iterables;

use Closure;
use IteratorAggregate;
use Override;
use Traversable;

/**
 * Transforms each item in the iterable. Keys will be preserved.
 *
 * @template TKey
 * @template TValue
 * @template TTransformed
 *
 * @implements IteratorAggregate<TKey, TTransformed>
 */
readonly class TransformIterable implements IteratorAggregate {
    public function __construct(
        /**
         * @var iterable<TKey, TValue>
         */
        protected iterable $iterable,
        /**
         * @var Closure(TValue, TKey): TTransformed
         */
        protected Closure $callback,
    ) {
        // empty
    }

    #[Override]
    public function getIterator(): Traversable {
        foreach ($this->iterable as $key => $value) {
            yield $key => ($this->callback)($value, $key);
        }
    }
}
