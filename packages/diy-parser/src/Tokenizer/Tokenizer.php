<?php declare(strict_types = 1);

namespace LastDragon_ru\DiyParser\Tokenizer;

use BackedEnum;
use LastDragon_ru\DiyParser\Streams\TokenStream;
use LastDragon_ru\DiyParser\Streams\TokenUnescapeStream;

/**
 * @template TToken of BackedEnum
 */
readonly class Tokenizer {
    public function __construct(
        /**
         * @var list<class-string<TToken>>|class-string<TToken>
         */
        protected array|string $tokens,
        /**
         * @var TToken
         */
        protected BackedEnum $string,
        /**
         * @var TToken
         */
        protected ?BackedEnum $escape = null,
    ) {
        // empty
    }

    /**
     * @param iterable<mixed, string> $stream
     *
     * @return iterable<mixed, Token<TToken>>
     */
    public function tokenize(iterable $stream, int $offset = 0): iterable {
        $stream = new TokenStream($stream, $this->tokens, $this->string, offset: $offset);

        if ($this->escape !== null) {
            $stream = new TokenUnescapeStream($stream, $this->string, $this->escape);
        }

        return $stream;
    }
}
