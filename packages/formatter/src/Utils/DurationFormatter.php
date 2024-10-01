<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Utils;

use DateInterval;

use function abs;
use function array_key_exists;
use function array_reduce;
use function floor;
use function is_float;
use function iterator_to_array;
use function mb_strlen;
use function pow;
use function round;
use function str_pad;

use const STR_PAD_LEFT;

/**
 * Format the duration according to the pattern.
 *
 * The syntax is the same as [ICU Date/Time format syntax](https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax).
 *
 * | Symbol | Meaning                       |
 * |--------|-------------------------------|
 * | `y`    | years                         |
 * | `M`    | months                        |
 * | `d`    | days                          |
 * | `H`    | hours                         |
 * | `m`    | minutes                       |
 * | `s`    | seconds                       |
 * | `S`    | fractional seconds            |
 * | `z`    | negative sign (default `-`)   |
 * | `'`    | escape for text               |
 * | `''`   | two single quotes produce one |
 *
 * @internal
 */
class DurationFormatter {
    final protected const SecondsInMinute = 60;
    final protected const SecondsInHour   = 60 * self::SecondsInMinute;
    final protected const SecondsInDay    = 24 * self::SecondsInHour;
    final protected const SecondsInMonth  = 30 * self::SecondsInDay;
    final protected const SecondsInYear   = 365 * self::SecondsInDay;

    public function __construct(
        protected readonly string $pattern,
    ) {
        // empty
    }

    public static function getTimestamp(DateInterval $interval): float {
        return ($interval->invert !== 0 ? -1 : 1) * (0
                + $interval->y * self::SecondsInYear
                + $interval->m * self::SecondsInMonth
                + $interval->d * self::SecondsInDay
                + $interval->h * self::SecondsInHour
                + $interval->i * self::SecondsInMinute
                + $interval->s
                + $interval->f);
    }

    public function format(float|int $value): string {
        $formatted = '';
        $tokens    = iterator_to_array(new UnicodeDateTimeFormatParser($this->pattern));
        $units     = $this->prepare($tokens, abs($value), [
            'y' => self::SecondsInYear,
            'M' => self::SecondsInMonth,
            'd' => self::SecondsInDay,
            'H' => self::SecondsInHour,
            'm' => self::SecondsInMinute,
            's' => 1,
            'S' => null,
        ]);

        foreach ($tokens as $token) {
            $formatted .= match ($token->pattern) {
                'z'     => $value < 0 ? '-' : '',
                "'"     => $token->value,
                default => isset($units[$token->pattern])
                    ? $this->value($units[$token->pattern], mb_strlen($token->value))
                    : '',
            };
        }

        return $formatted;
    }

    /**
     * @param array<array-key, UnicodeDateTimeFormatToken> $tokens
     * @param array<string, int|null>                      $units
     *
     * @return array<string, float|int>
     */
    private function prepare(array $tokens, float|int $value, array $units): array {
        // Calculate values
        $values   = [];
        $patterns = array_reduce(
            $tokens,
            static function (array $used, UnicodeDateTimeFormatToken $token) use ($units): array {
                if (array_key_exists($token->pattern, $units)) {
                    $used[$token->pattern] = true;
                }

                return $used;
            },
            [],
        );

        foreach ($units as $pattern => $multiplier) {
            if (!isset($patterns[$pattern])) {
                continue;
            }

            if ($multiplier !== null) {
                $values[$pattern] = (int) floor($value / $multiplier);
                $value            = $value - $values[$pattern] * $multiplier;
            } else {
                $values[$pattern] = $value;
                $value            = 0;
            }
        }

        // Return
        return $values;
    }

    private function value(float|int $value, int $length): string {
        // Float?
        if (is_float($value)) {
            $value = (int) round(($value - (int) $value) * pow(10, $length));
        }

        // Width?
        $value = str_pad((string) $value, $length, '0', STR_PAD_LEFT);

        // Return
        return $value;
    }
}
