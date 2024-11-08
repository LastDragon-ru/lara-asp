<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Formatters\DateTime;

use DateTimeInterface;
use DateTimeZone;
use IntlDateFormatter;
use IntlException;
use IntlTimeZone;
use InvalidArgumentException;
use NumberFormatter;

/**
 * @internal
 * @see NumberFormatter
 */
class Formatter {
    protected readonly IntlDateFormatter $formatter;

    public function __construct(
        protected readonly string $locale,
        protected readonly IntlTimeZone|DateTimeZone|string|null $timezone,
        ?Options ...$options,
    ) {
        // Collect options
        $dateType = null;
        $timeType = null;
        $pattern  = null;

        foreach ($options as $intl) {
            if ($intl === null) {
                continue;
            }

            $dateType ??= $intl->dateType;
            $timeType ??= $intl->timeType;
            $pattern  ??= $intl->pattern;
        }

        // Possible?
        if ($dateType === null) {
            throw new InvalidArgumentException('The `$dateType` in unknown.');
        }

        if ($timeType === null) {
            throw new InvalidArgumentException('The `$timeType` in unknown.');
        }

        // Create
        $pattern         = $pattern !== '' ? $pattern : null;
        $this->formatter = new IntlDateFormatter($locale, $dateType, $timeType, $timezone, null, $pattern);
    }

    public function format(DateTimeInterface $value): string {
        $formatted = $this->formatter->format($value);

        if ($formatted === false) {
            throw new IntlException($this->formatter->getErrorMessage(), $this->formatter->getErrorCode());
        }

        return $formatted;
    }
}
