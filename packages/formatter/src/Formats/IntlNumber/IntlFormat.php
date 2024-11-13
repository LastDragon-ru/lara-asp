<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Formats\IntlNumber;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Formatter\Contracts\Format;
use LastDragon_ru\LaraASP\Formatter\Formatter;
use NumberFormatter;
use OutOfBoundsException;

use function sprintf;

/**
 * @see NumberFormatter
 *
 * @template TOptions of IntlOptions
 * @template TValue
 *
 * @implements Format<TOptions, TValue>
 */
abstract class IntlFormat implements Format {
    protected readonly NumberFormatter $formatter;

    /**
     * @param list<TOptions|null> $options
     */
    public function __construct(Formatter $formatter, array $options = []) {
        // Collect options
        $style          = null;
        $pattern        = null;
        $symbols        = [];
        $attributes     = [];
        $textAttributes = [];

        foreach ($options as $intl) {
            if ($intl === null) {
                continue;
            }

            $style         ??= $intl->style;
            $pattern       ??= $intl->pattern;
            $symbols        += $intl->symbols;
            $attributes     += $intl->attributes;
            $textAttributes += $intl->textAttributes;
        }

        // Possible?
        if ($style === null) {
            throw new InvalidArgumentException('The `$style` in unknown.');
        }

        // Create
        $locale          = $formatter->getLocale();
        $pattern         = $pattern !== '' ? $pattern : null;
        $this->formatter = new NumberFormatter($locale, $style, $pattern);

        // Apply
        foreach ($attributes as $attribute => $value) {
            if (!$this->formatter->setAttribute($attribute, $value)) {
                throw new OutOfBoundsException(
                    sprintf(
                        'Attribute `%s` is unknown/invalid.',
                        $attribute,
                    ),
                );
            }
        }

        foreach ($symbols as $symbol => $value) {
            if (!$this->formatter->setSymbol($symbol, $value)) {
                throw new OutOfBoundsException(
                    sprintf(
                        'Symbol `%s` is unknown/invalid.',
                        $symbol,
                    ),
                );
            }
        }

        foreach ($textAttributes as $attribute => $value) {
            if (!$this->formatter->setTextAttribute($attribute, $value)) {
                throw new OutOfBoundsException(
                    sprintf(
                        'TextAttribute `%s` is unknown/invalid.',
                        $attribute,
                    ),
                );
            }
        }
    }
}
