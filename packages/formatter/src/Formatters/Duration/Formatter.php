<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Formatters\Duration;

use DateInterval;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Formatter\Formats\IntlNumber\IntlNumberFormat;
use LastDragon_ru\LaraASP\Formatter\Formats\IntlNumber\IntlOptions;
use LastDragon_ru\LaraASP\Formatter\Formatter as GlobalFormatter;
use NumberFormatter;

/**
 * @internal
 */
class Formatter {
    protected readonly PatternFormatter|IntlNumberFormat $formatter;

    public function __construct(
        GlobalFormatter $formatter,
        IntlOptions|PatternOptions|null ...$options,
    ) {
        // Collect options
        $intl      = [
            new IntlOptions(NumberFormatter::DURATION),
        ];
        $pattern   = null;
        $isPattern = null;

        foreach ($options as $option) {
            if ($option === null) {
                continue;
            }

            $isPattern ??= $option instanceof PatternOptions;
            $pattern   ??= $option->pattern;

            if ($option instanceof IntlOptions) {
                $intl[] = $option;
            }
        }

        // Possible?
        if ($isPattern === null) {
            throw new InvalidArgumentException('The formatter type in unknown.');
        }

        if ($isPattern === true && $pattern === null) {
            throw new InvalidArgumentException('The `$patten` in unknown.');
        }

        // Create
        $this->formatter = $isPattern === false
            ? new IntlNumberFormat($formatter, $intl)
            : new PatternFormatter($pattern);
    }

    public function format(DateInterval|float|int $value): string {
        $value     = $value instanceof DateInterval ? PatternFormatter::getTimestamp($value) : $value;
        $formatted = $this->formatter instanceof PatternFormatter
            ? $this->formatter->format($value)
            : ($this->formatter)($value);

        return $formatted;
    }
}
