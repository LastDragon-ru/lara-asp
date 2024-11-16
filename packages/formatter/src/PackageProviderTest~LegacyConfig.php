<?php declare(strict_types = 1);

use LastDragon_ru\LaraASP\Core\Utils\ConfigMerger;
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
     * @see Formatter
     */
    'options' => [
        /**
         * This value has no effect inside the published config.
         */
        ConfigMerger::Strict => false,
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
        /**
         * This value has no effect inside the published config.
         */
        ConfigMerger::Strict => false,
    ],

    /**
     * Settings for concrete locales
     * ---------------------------------------------------------------------
     * For date/time please use ICU, see
     * https://unicode-org.github.io/icu/userguide/format_parse/datetime/#formatting-dates-and-times
     */
    'locales' => [
        /**
         * This value has no effect inside the published config.
         */
        ConfigMerger::Strict => false,
        'ru_RU'              => [
            // ...
        ],
    ],
];
