<?php declare(strict_types = 1);

use LastDragon_ru\LaraASP\Formatter\Formatter;

/**
 * -----------------------------------------------------------------------------
 * Formatter Settings
 * -----------------------------------------------------------------------------
 */

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
     * Settings for all locales
     * ---------------------------------------------------------------------
     * You can define a custom pattern for all locales here.
     *
     * For date/time please use ICU, see
     * https://unicode-org.github.io/icu/userguide/format_parse/datetime/#formatting-dates-and-times
     */
    // 'all' => [
    //     Formatter::Time => [
    //         'custom' => 'HH:mm:ss.SSS',
    //     ],
    // ],

    /**
     * Settings for concrete locales
     * ---------------------------------------------------------------------
     * For date/time please use ICU, see
     * https://unicode-org.github.io/icu/userguide/format_parse/datetime/#formatting-dates-and-times
     */
    'locales' => [
        // For specific Locale
        // 'ru_RU' => [
        //     Formatter::Time => [
        //         'custom' => 'HH:mm:ss',
        //     ],
        // ],
    ],
];
