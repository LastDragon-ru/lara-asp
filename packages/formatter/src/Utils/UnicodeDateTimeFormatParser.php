<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Utils;

use Iterator;
use IteratorAggregate;
use Override;
use Traversable;

use function mb_strlen;
use function mb_substr;
use function preg_match;
use function preg_split;
use function strtr;

use const PREG_SPLIT_DELIM_CAPTURE;
use const PREG_SPLIT_NO_EMPTY;

/**
 * Parse Date/Time Format string.
 *
 * @see https://unicode-org.github.io/icu/userguide/format_parse/datetime/#formatting-dates-and-times
 *
 * @internal
 *
 * @implements IteratorAggregate<int, UnicodeDateTimeFormatToken>
 */
class UnicodeDateTimeFormatParser implements IteratorAggregate {
    public function __construct(
        protected readonly string $pattern,
    ) {
        // empty
    }

    #[Override]
    public function getIterator(): Traversable {
        $text     = null;
        $escape   = "'";
        $replace  = [
            $escape.$escape => $escape,
        ];
        $inEscape = false;

        foreach ($this->tokenize($this->pattern) as $token => $value) {
            $isEscape  = $token === $escape;
            $isPattern = !$isEscape && !$inEscape && preg_match('/[a-z]+/i', $token);

            if ($inEscape) {
                if ($isEscape) {
                    $value    = mb_substr($value, 1);
                    $inEscape = mb_strlen($value) % 2 !== 0;
                }

                $text .= $value;
            } elseif ($isEscape) {
                if (mb_strlen($value) % 2 !== 0) {
                    $text    .= mb_substr($value, 1);
                    $inEscape = true;
                } else {
                    $text .= $value;
                }
            } elseif ($isPattern) {
                if ($text) {
                    yield new UnicodeDateTimeFormatToken($escape, strtr($text, $replace));

                    $text = null;
                }

                yield new UnicodeDateTimeFormatToken($token, $value);
            } else {
                $text .= $value;
            }
        }

        if ($text) {
            yield new UnicodeDateTimeFormatToken($escape, strtr($text, $replace));
        }

        yield from [];
    }

    /**
     * @return Iterator<string, string>
     */
    private function tokenize(string $pattern): Iterator {
        // Split into char & string of the same chars
        $strings = preg_split('/((.)\g{-1}*)/um', $pattern, flags: PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

        if (!$strings) {
            yield from [];

            return;
        }

        // Group into char & string
        $value = null;

        foreach ($strings as $string) {
            if ($value === null) {
                $value = $string;
            } else {
                yield $string => $value;

                $value = null;
            }
        }
    }
}
