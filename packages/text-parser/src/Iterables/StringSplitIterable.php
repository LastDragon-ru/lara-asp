<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Iterables;

use Generator;
use IteratorAggregate;
use LastDragon_ru\TextParser\Package;
use Override;
use Traversable;

use function array_map;
use function array_pop;
use function array_reverse;
use function array_unique;
use function end;
use function implode;
use function max;
use function mb_strlen;
use function preg_quote;
use function preg_split;
use function reset;
use function strval;
use function usort;

use const PREG_SPLIT_DELIM_CAPTURE;
use const PREG_SPLIT_NO_EMPTY;
use const PREG_SPLIT_OFFSET_CAPTURE;

/**
 * Splits iterable of strings by the separator(s) where the key is an offset of
 * the string/separator.
 *
 * @implements IteratorAggregate<int, string>
 */
readonly class StringSplitIterable implements IteratorAggregate {
    protected string $regexp;
    protected int    $longest;

    public function __construct(
        /**
         * @var iterable<mixed, string>
         */
        protected iterable $iterable,
        /**
         * @var list<string|int>
         */
        protected array $separators,
        /**
         * Internal buffer size in characters (not bytes!).
         *
         * @var positive-int|null
         */
        protected ?int $buffer = null,
        protected int $offset = 0,
        protected bool $caseSensitive = true,
    ) {
        [$this->regexp, $this->longest] = $this->prepare($this->separators);
    }

    #[Override]
    public function getIterator(): Traversable {
        $buffer = '';
        $offset = $this->offset;
        $limit  = max($this->buffer ?? Package::BufferCharacters, 2 * $this->longest);

        foreach ($this->iterable as $string) {
            $buffer .= $string;

            if (mb_strlen($buffer, Package::Encoding) >= $limit) {
                $items   = $this->split($buffer);
                $length  = 0;
                $skipped = [];

                while ($items !== [] && $length < $this->longest) {
                    $last      = array_pop($items)[0];
                    $length    = mb_strlen($last, Package::Encoding);
                    $skipped[] = $last;
                }

                yield from $this->iterator($offset, $items);

                $last   = $items !== [] ? end($items) : ['', 0];
                $offset = $offset + mb_strlen($last[0], Package::Encoding) + $last[1];
                $buffer = implode('', array_reverse($skipped));
            }
        }

        yield from $this->iterator($offset, $this->split($buffer));
    }

    /**
     * @param list<string|int> $separators
     *
     * @return array{string, int}
     */
    protected function prepare(array $separators): array {
        // Remove duplicates
        $separators = array_map(strval(...), $separators);
        $separators = array_unique($separators);

        // Sort (longest must be first)
        usort($separators, static fn ($a, $b) => mb_strlen($b, Package::Encoding) <=> mb_strlen($a, Package::Encoding));

        // Longest
        $longest = mb_strlen((string) reset($separators), Package::Encoding);

        // Regexp
        $quoted = array_map(preg_quote(...), $separators);
        $regexp = '#('.implode('|', $quoted).')#u'.($this->caseSensitive ? '' : 'i');

        return [$regexp, $longest];
    }

    /**
     * @return list<array{string, int<0, max>}>
     */
    private function split(string $string): array {
        /** @see https://github.com/phpstan/phpstan-strict-rules/issues/268 */

        $flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE;
        $split = preg_split($this->regexp, $string, -1, $flags);
        $split = $split !== false ? $split : [];

        return $split;
    }

    /**
     * @param list<array{string, int}> $items
     *
     * @return Generator<int, string>
     */
    private function iterator(int $offset, array $items): Generator {
        foreach ($items as $item) {
            yield $offset + $item[1] => $item[0];
        }
    }
}
