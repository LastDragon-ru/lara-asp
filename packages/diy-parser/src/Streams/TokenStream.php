<?php declare(strict_types = 1);

namespace LastDragon_ru\DiyParser\Streams;

use BackedEnum;
use IteratorAggregate;
use LastDragon_ru\DiyParser\Tokenizer\Token;
use Override;
use Traversable;
use UnitEnum;

use function array_keys;

/**
 * Searches tokens inside the stream of strings.
 *
 * @template TToken of BackedEnum
 *
 * @implements IteratorAggregate<int, Token<TToken>>
 */
readonly class TokenStream implements IteratorAggregate {
    /**
     * @var array<array-key, TToken>
     */
    protected array $map;

    public function __construct(
        /**
         * @var iterable<mixed, string>
         */
        protected iterable $stream,
        /**
         * @var list<class-string<TToken>>|class-string<TToken>
         */
        protected array|string $tokens,
        /**
         * @var TToken
         */
        protected UnitEnum $string,
        /**
         * Internal buffer size in characters (not bytes!).
         *
         * @var positive-int|null
         */
        protected ?int $buffer = null,
        protected int $offset = 0,
    ) {
        $this->map = $this->map((array) $this->tokens);
    }

    #[Override]
    public function getIterator(): Traversable {
        $stream = new StringSplitStream($this->stream, array_keys($this->map), $this->buffer, $this->offset, true);

        foreach ($stream as $offset => $value) {
            yield new Token($this->map[$value] ?? $this->string, $value, $offset);
        }
    }

    /**
     * @template T of BackedEnum
     *
     * @param list<class-string<T>> $enums
     *
     * @return array<array-key, T>
     */
    protected function map(array $enums): array {
        // todo(parser): Need to warning/error if
        //      * two or more enums with the same value (only last will be used)
        //      * values have intersections, eg `(` and `!(` (the shortest value will be lost)

        $map = [];

        foreach ($enums as $enum) {
            foreach ($enum::cases() as $case) {
                $map[$case->value] = $case;
            }
        }

        unset($map['']);

        return $map;
    }
}
