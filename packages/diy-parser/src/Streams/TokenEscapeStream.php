<?php declare(strict_types = 1);

namespace LastDragon_ru\DiyParser\Streams;

use BackedEnum;
use IteratorAggregate;
use LastDragon_ru\DiyParser\Tokenizer\Token;
use Override;
use Traversable;
use UnitEnum;

/**
 * Escapes tokens (= adds {@see self::$escape} before each non {@see self::$string} token).
 *
 * @template TToken of UnitEnum
 *
 * @implements IteratorAggregate<int, Token<TToken>>
 */
readonly class TokenEscapeStream implements IteratorAggregate {
    public function __construct(
        /**
         * @var iterable<mixed, Token<TToken>>
         */
        protected iterable $stream,
        /**
         * @var TToken
         */
        protected UnitEnum $string,
        /**
         * @var TToken
         */
        protected UnitEnum $escape,
    ) {
        // empty
    }

    #[Override]
    public function getIterator(): Traversable {
        foreach ($this->stream as $item) {
            if ($item->name !== $this->string) {
                yield new Token(
                    $this->escape,
                    $this->escape instanceof BackedEnum ? (string) $this->escape->value : '',
                    $item->offset,
                );
            }

            yield $item;
        }
    }
}
