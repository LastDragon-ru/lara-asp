<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Formats\IntlNumber;

use NumberFormatter;

class IntlCurrencyOptions extends IntlNumberOptions {
    /**
     * @param NumberFormatter::*|null $style
     * @param array<int, string>      $symbols
     * @param array<int, int|float>   $attributes
     * @param array<int, string>      $textAttributes
     */
    public function __construct(
        ?int $style = null,
        ?string $pattern = null,
        array $symbols = [],
        array $attributes = [],
        array $textAttributes = [],
        /**
         * @var non-empty-string|null
         */
        public ?string $currency = null,
    ) {
        parent::__construct($style, $pattern, $symbols, $attributes, $textAttributes);
    }
}
