<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Formatters\Number;

use IntlException;
use InvalidArgumentException;
use NumberFormatter;
use OutOfBoundsException;

use function sprintf;

/**
 * @internal
 * @see NumberFormatter
 */
class Formatter {
    protected readonly NumberFormatter $formatter;

    public function __construct(
        protected readonly string $locale,
        ?Options ...$options,
    ) {
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

    public function formatNumber(float|int $value): string {
        $formatted = $this->formatter->format($value);

        if ($formatted === false) {
            throw new IntlException($this->formatter->getErrorMessage(), $this->formatter->getErrorCode());
        }

        return $formatted;
    }

    public function formatCurrency(float|int $value, ?string $currency = null): string {
        $currency ??= $this->formatter->getTextAttribute(NumberFormatter::CURRENCY_CODE);
        $formatted = $this->formatter->formatCurrency($value, $currency);

        if ($formatted === false) {
            throw new IntlException($this->formatter->getErrorMessage(), $this->formatter->getErrorCode());
        }

        return $formatted;
    }
}
