<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Formats\IntlNumber;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Formatter\Contracts\Format;
use LastDragon_ru\LaraASP\Formatter\Formatter;
use LastDragon_ru\LaraASP\Formatter\PackageConfig;
use NumberFormatter;
use OutOfBoundsException;

use function sprintf;

/**
 * @see NumberFormatter
 *
 * @template TOptions of IntlNumberOptions|null
 * @template TValue
 *
 * @implements Format<TOptions, TValue>
 */
abstract readonly class IntlFormat implements Format {
    protected NumberFormatter $formatter;

    /**
     * @param list<TOptions|null> $options
     */
    public function __construct(PackageConfig $config, Formatter $formatter, array $options = []) {
        // Collect options
        $style          = null;
        $pattern        = null;
        $symbols        = [];
        $attributes     = [];
        $textAttributes = [];
        $options[]      = $config->getInstance()->intl->number ?? null;

        foreach ($options as $option) {
            if ($option === null) {
                continue;
            }

            $style         ??= $option->style;
            $pattern       ??= $option->pattern;
            $symbols        += $option->symbols;
            $attributes     += $option->attributes;
            $textAttributes += $option->textAttributes;
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
