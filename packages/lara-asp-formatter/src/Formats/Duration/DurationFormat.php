<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Formats\Duration;

use DateInterval;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Formatter\Contracts\Format;
use LastDragon_ru\LaraASP\Formatter\Utils\Duration;
use LastDragon_ru\LaraASP\Formatter\Utils\UnicodeDateTimeFormatParser;
use LastDragon_ru\LaraASP\Formatter\Utils\UnicodeDateTimeFormatToken;
use Override;

use function abs;
use function array_key_exists;
use function array_reduce;
use function floor;
use function is_float;
use function iterator_to_array;
use function mb_str_pad;
use function mb_strlen;
use function pow;
use function round;

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
 * @implements Format<DurationOptions, DateInterval|float|int|null>
 */
readonly class DurationFormat implements Format {
    protected string $pattern;

    /**
     * @param list<DurationOptions|null> $options
     */
    public function __construct(array $options = []) {
        // Collect options
        $pattern = null;

        foreach ($options as $option) {
            if ($option === null) {
                continue;
            }

            $pattern ??= $option->pattern;
        }

        // Possible?
        if ($pattern === null) {
            throw new InvalidArgumentException('The `$patten` in unknown.');
        }

        // Save
        $this->pattern = $pattern;
    }

    #[Override]
    public function __invoke(mixed $value): string {
        $formatted = '';
        $tokens    = iterator_to_array(new UnicodeDateTimeFormatParser($this->pattern));
        $value     = Duration::getTimestamp($value);
        $units     = $this->prepare($tokens, abs($value), [
            'y' => Duration::SecondsInYear,
            'M' => Duration::SecondsInMonth,
            'd' => Duration::SecondsInDay,
            'H' => Duration::SecondsInHour,
            'm' => Duration::SecondsInMinute,
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
        $value = mb_str_pad((string) $value, $length, '0', STR_PAD_LEFT);

        // Return
        return $value;
    }
}
