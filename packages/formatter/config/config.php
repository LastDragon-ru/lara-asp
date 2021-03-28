<?php declare(strict_types = 1);

use LastDragon_ru\LaraASP\Core\Utils\ConfigMerger;

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
        // This value has no effect inside the published config.
        ConfigMerger::Strict => false,

        // Fraction digits for decimal()
        // Formatter::Decimal => 2,

        // Default custom time format, you can also use
        // - {@link \IntlDateFormatter::SHORT} (default)
        // - {@link \IntlDateFormatter::FULL}
        // - {@link \IntlDateFormatter::LONG}
        // - {@link \IntlDateFormatter::MEDIUM}
        // Formatter::Time => 'custom',

        // Global Attributes for {@link \NumberFormatter::setAttribute()}
        'intl_attributes'    => [
            NumberFormatter::ROUNDING_MODE => NumberFormatter::ROUND_HALFUP,
        ],

        // Global Symbols for {@link \NumberFormatter::setSymbol()}
        // 'intl_symbols' => [
        //     // ...
        // ],

        // Global Attributes for {@link \NumberFormatter::setTextAttribute()}
        // 'intl_text_attributes' => [
        //     // ...
        // ],
    ],

    /**
     * Settings for all locales
     * ---------------------------------------------------------------------
     * You can define a custom pattern for all locales here.
     *
     * For date/time please use ICU, see
     * https://unicode-org.github.io/icu/userguide/format_parse/datetime/#formatting-dates-and-times
     */
    'all'     => [
        // This value has no effect inside the published config.
        ConfigMerger::Strict => false,

        // Custom time format for all locales
        // Formatter::Time        => [
        //     'custom' => 'HH:mm:ss.SSS',
        // ],

        // Intl properties for all locales (will be merged with `options`)
        // Formatter::Decimal => [
        //     'intl_attributes'      => [],
        //     'intl_symbols'         => [],
        //     'intl_text_attributes' => [],
        // ],
    ],

    /**
     * Settings for concrete locales
     * ---------------------------------------------------------------------
     * For date/time please use ICU, see
     * https://unicode-org.github.io/icu/userguide/format_parse/datetime/#formatting-dates-and-times
     */
    'locales' => [
        // This value has no effect inside the published config.
        ConfigMerger::Strict => false,

        // 'ru_RU' => [
        //     // Custom time format for specific Locale
        //     Formatter::Time => [
        //         'custom' => 'HH:mm:ss',
        //     ],
        //
        //     // Intl properties for specific Locale (will be merged with all`)
        //     Formatter::Decimal => [
        //         'intl_attributes'      => [],
        //         'intl_symbols'         => [],
        //         'intl_text_attributes' => [],
        //     ],
        // ],
    ],
];
