<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Formats\IntlDateTime;

use DateTimeInterface;
use IntlDateFormatter;
use IntlException;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Formatter\Contracts\Format;
use LastDragon_ru\LaraASP\Formatter\Formatter;
use Override;

use function is_null;

/**
 * @see IntlDateFormatter
 *
 * @implements Format<IntlDateTimeOptions, DateTimeInterface|null>
 */
readonly class IntlDateTimeFormat implements Format {
    protected IntlDateFormatter $formatter;

    /**
     * @param list<IntlDateTimeOptions|null> $options
     */
    public function __construct(Formatter $formatter, array $options = []) {
        // Collect options
        $dateType = null;
        $timeType = null;
        $pattern  = null;

        foreach ($options as $option) {
            if ($option === null) {
                continue;
            }

            $dateType ??= $option->dateType;
            $timeType ??= $option->timeType;
            $pattern  ??= $option->pattern;
        }

        // Possible?
        if ($dateType === null) {
            throw new InvalidArgumentException('The `$dateType` in unknown.');
        }

        if ($timeType === null) {
            throw new InvalidArgumentException('The `$timeType` in unknown.');
        }

        // Create
        $locale          = $formatter->getLocale();
        $pattern         = $pattern !== '' ? $pattern : null;
        $timezone        = $formatter->getTimezone();
        $this->formatter = new IntlDateFormatter($locale, $dateType, $timeType, $timezone, null, $pattern);
    }

    #[Override]
    public function __invoke(mixed $value): string {
        // Null?
        if (is_null($value)) {
            return '';
        }

        // Format
        $formatted = $this->formatter->format($value);

        if ($formatted === false) {
            throw new IntlException($this->formatter->getErrorMessage(), $this->formatter->getErrorCode());
        }

        return $formatted;
    }
}
