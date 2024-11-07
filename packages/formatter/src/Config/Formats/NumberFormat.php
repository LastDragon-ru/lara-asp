<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Config\Formats;

use LastDragon_ru\LaraASP\Formatter\Config\IntlNumberOptions;
use NumberFormatter;

/**
 * @see NumberFormatter
 */
class NumberFormat extends IntlNumberOptions {
    /**
     * @param ?NumberFormatter::*   $style
     * @param array<int, string>    $symbols
     * @param array<int, int|float> $attributes
     * @param array<int, string>    $textAttributes
     */
    public function __construct(
        ?int $style = null,
        ?string $pattern = null,
        array $symbols = [],
        array $attributes = [],
        array $textAttributes = [],
    ) {
        parent::__construct($style, $pattern, $symbols, $attributes, $textAttributes);
    }
}
