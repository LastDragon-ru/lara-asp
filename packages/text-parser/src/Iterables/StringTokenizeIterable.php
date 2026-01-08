<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Iterables;

use BackedEnum;
use IteratorAggregate;
use LastDragon_ru\TextParser\Tokenizer\Token;
use Override;
use Traversable;

use function array_keys;

/**
 * Searches tokens inside the iterable of strings.
 *
 * @template TToken of BackedEnum
 *
 * @implements IteratorAggregate<int, Token<TToken>>
 */
readonly class StringTokenizeIterable implements IteratorAggregate {
    /**
     * @var array<array-key, TToken>
     */
    protected array $map;

    public function __construct(
        /**
         * @var iterable<mixed, string>
         */
        protected iterable $iterable,
        /**
         * @var list<class-string<TToken>>|class-string<TToken>
         */
        protected array|string $tokens,
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
        $iterable = new StringSplitIterable($this->iterable, array_keys($this->map), $this->buffer, $this->offset, true);

        foreach ($iterable as $offset => $value) {
            yield new Token($this->map[$value] ?? null, $value, $offset);
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
