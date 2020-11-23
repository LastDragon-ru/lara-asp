<?php declare(strict_types = 1);

/**
 * -----------------------------------------------------------------------------
 * Formatter Settings
 * -----------------------------------------------------------------------------
 */

use LastDragon_ru\LaraASP\Formatter\Formatter;

return [
    /**
     * Options
     * -------------------------------------------------------------------------
     * Default options for specific formats.
     *
     * @see LastDragon_ru\LaraASP\Formatter\Formatter
     */
    'options' => [
        // Fraction digits for decimal()
        // Formatter::Decimal => 2,

        // Additional custom time format
        // Formatter::Time => 'custom',
    ],

    /**
     * Locales overrides
     * ---------------------------------------------------------------------
     * You can define a custom pattern for all ('all' section) and overwrite it
     * for a specific locale.
     *
     * For date/time please use ICU, see
     * https://unicode-org.github.io/icu/userguide/format_parse/datetime/#formatting-dates-and-times
     */
    'locales'   => [
        // For All
        // 'all' => [
        //     Formatter::Time => [
        //         'custom' => 'HH:mm:ss.SSS',
        //     ],
        // ],

        // For specific Locale
        // 'ru_RU' => [
        //     Formatter::Time => [
        //         'custom' => 'HH:mm:ss',
        //     ],
        // ],
    ],
];
