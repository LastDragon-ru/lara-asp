<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Iterables;

use IteratorAggregate;
use LastDragon_ru\TextParser\Tokenizer\Token;
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
readonly class TokenUnescapeIterable implements IteratorAggregate {
    public function __construct(
        /**
         * @var iterable<mixed, Token<TToken>>
         */
        protected iterable $iterable,
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

        foreach ($this->iterable as $token) {
            if ($escaped !== null) {
                if ($this->isEscapable($token)) {
                    /** @phpstan-ignore generator.valueType (`null` is fine) */
                    yield new Token(null, $token->value, $token->offset);
                } else {
                    yield $escaped;
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

    /**
     * @param Token<TToken> $token
     */
    protected function isEscapable(Token $token): bool {
        return $token->name !== null;
    }
}
