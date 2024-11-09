<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Formatters\Duration;

use DateInterval;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Formatter\Formatters\Number\Formatter as IntlFormatter;
use LastDragon_ru\LaraASP\Formatter\Formatters\Number\Options as IntlOptions;
use NumberFormatter;

/**
 * @internal
 */
class Formatter {
    protected readonly PatternFormatter|IntlFormatter $formatter;

    public function __construct(
        protected readonly string $locale,
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
            ? new IntlFormatter($locale, ...$intl)
            : new PatternFormatter($pattern);
    }

    public function format(DateInterval|float|int $value): string {
        $value     = $value instanceof DateInterval ? PatternFormatter::getTimestamp($value) : $value;
        $formatted = $this->formatter->format($value);

        return $formatted;
    }
}
