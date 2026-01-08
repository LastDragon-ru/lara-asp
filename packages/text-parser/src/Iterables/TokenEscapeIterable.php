<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Iterables;

use BackedEnum;
use IteratorAggregate;
use LastDragon_ru\TextParser\Tokenizer\Token;
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
readonly class TokenEscapeIterable implements IteratorAggregate {
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
        foreach ($this->iterable as $token) {
            if ($this->isEscapable($token)) {
                yield new Token(
                    $this->escape,
                    $this->escape instanceof BackedEnum ? (string) $this->escape->value : '',
                    $token->offset,
                );
            }

            yield $token;
        }
    }

    /**
     * @param Token<TToken> $token
     */
    protected function isEscapable(Token $token): bool {
        return $token->name !== null;
    }
}
