<?php declare(strict_types = 1);

namespace LastDragon_ru\DiyParser\Streams;

use IteratorAggregate;
use LastDragon_ru\DiyParser\Tokenizer\Token;
use Override;
use Traversable;
use UnitEnum;

/**
 * Converts escaped tokens into string.
 *
 * @template TToken of UnitEnum
 *
 * @implements IteratorAggregate<int, Token<TToken>>
 */
readonly class TokenUnescapeStream implements IteratorAggregate {
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
        $escaped = null;

        foreach ($this->stream as $token) {
            if ($escaped !== null) {
                if ($token->name !== $this->string) {
                    yield new Token($this->string, $token->value, $token->offset);
                } else {
                    yield $token;
                }

                $escaped = null;
            } elseif ($token->name === $this->escape) {
                $escaped = $token;
            } else {
                yield $token;
            }
        }
    }
}
