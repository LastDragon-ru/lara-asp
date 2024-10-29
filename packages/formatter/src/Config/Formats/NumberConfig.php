<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Config\Formats;

use LastDragon_ru\LaraASP\Formatter\Config\IntlOptions;

class NumberConfig extends IntlOptions {
    /**
     * @param array<int, string>    $symbols
     * @param array<int, int|float> $attributes
     * @param array<int, string>    $textAttributes
     */
    public function __construct(
        /**
         * @var array<non-empty-string, NumberFormat>
         */
        public array $formats = [],
        array $symbols = [],
        array $attributes = [],
        array $textAttributes = [],
    ) {
        parent::__construct($symbols, $attributes, $textAttributes);
    }
}
