<?php declare(strict_types = 1);

namespace LastDragon_ru\DiyParser\Streams;

use BackedEnum;
use IteratorAggregate;
use Override;
use Stringable;
use Traversable;

/**
 * Converts values into string. Keys will be preserved.
 *
 * @template TKey of mixed
 * @template TValue of Stringable|BackedEnum|string|int|null
 *
 * @implements IteratorAggregate<TKey, string>
 */
readonly class StringifyStream implements IteratorAggregate {
    public function __construct(
        /**
         * @var iterable<TKey, TValue>
         */
        protected iterable $stream,
    ) {
        // empty
    }

    #[Override]
    public function getIterator(): Traversable {
        foreach ($this->stream as $key => $value) {
            yield $key => match (true) {
                $value instanceof BackedEnum => (string) $value->value,
                default                      => (string) $value,
            };
        }
    }
}
