<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Config\Formats;

use LastDragon_ru\LaraASP\Formatter\Config\IntlNumberOptions;
use NumberFormatter;

/**
 * @see NumberFormatter::formatCurrency()
 */
class CurrencyFormat extends IntlNumberOptions {
    /**
     * @param array<int, string>    $symbols
     * @param array<int, int|float> $attributes
     * @param array<int, string>    $textAttributes
     */
    public function __construct(
        ?string $pattern = null,
        array $symbols = [],
        array $attributes = [],
        array $textAttributes = [],
    ) {
        parent::__construct(null, $pattern, $symbols, $attributes, $textAttributes);
    }
}
