<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Config\Formats;

use LastDragon_ru\LaraASP\Formatter\Config\IntlNumberOptions;
use NumberFormatter;

class NumberConfig extends IntlNumberOptions {
    /**
     * @param NumberFormatter::*|null $style
     * @param array<int, string>      $symbols
     * @param array<int, int|float>   $attributes
     * @param array<int, string>      $textAttributes
     */
    public function __construct(
        /**
         * @var array<non-empty-string, NumberFormat>
         */
        public array $formats = [],
        ?int $style = null,
        ?string $pattern = null,
        array $symbols = [],
        array $attributes = [],
        array $textAttributes = [],
    ) {
        parent::__construct($style, $pattern, $symbols, $attributes, $textAttributes);
    }
}
