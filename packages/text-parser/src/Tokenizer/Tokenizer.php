<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Tokenizer;

use BackedEnum;
use LastDragon_ru\TextParser\Iterables\StringTokenizeIterable;
use LastDragon_ru\TextParser\Iterables\TokenUnescapeIterable;

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
        protected ?BackedEnum $escape = null,
    ) {
        // empty
    }

    /**
     * @param iterable<mixed, string> $iterable
     *
     * @return iterable<mixed, Token<TToken>>
     */
    public function tokenize(iterable $iterable, int $offset = 0): iterable {
        $iterable = new StringTokenizeIterable($iterable, $this->tokens, offset: $offset);

        if ($this->escape !== null) {
            $iterable = new TokenUnescapeIterable($iterable, $this->escape);
        }

        return $iterable;
    }
}
