<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Config\Formats;

use LastDragon_ru\LaraASP\Formatter\Config\IntlOptions;
use NumberFormatter;

/**
 * @see NumberFormatter
 */
class DurationFormatIntl extends IntlOptions {
    /**
     * @param array<int, string>    $symbols
     * @param array<int, int|float> $attributes
     * @param array<int, string>    $textAttributes
     */
    public function __construct(
        /**
         * @see NumberFormatter::setPattern()
         */
        public ?string $pattern = null,
        array $symbols = [],
        array $attributes = [],
        array $textAttributes = [],
    ) {
        parent::__construct($symbols, $attributes, $textAttributes);
    }
}