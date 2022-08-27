<?php declare(strict_types = 1);

use LastDragon_ru\LaraASP\Core\Utils\ConfigMerger;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;

/**
 * -----------------------------------------------------------------------------
 * GraphQL Settings
 * -----------------------------------------------------------------------------
 */
return [
    /**
     * Settings for @searchBy directive.
     */
    'search_by' => [
        /**
         * Operators
         * ---------------------------------------------------------------------
         *
         * You can (re)define types and supported operators here.
         *
         * @var array<string, array<class-string<Operator>>>
         */
        'operators' => [
            // This value has no effect inside the published config.
            ConfigMerger::Replace => true,
        ],
    ],

    /**
     * These enums will be registered automatically. You can use key to specify
     * enum name.
     *
     * @see \LastDragon_ru\LaraASP\Core\Enum
     * @see \LastDragon_ru\LaraASP\Eloquent\Enum
     */
    'enums'     => [
        // This value has no effect inside the published config.
        ConfigMerger::Replace => true,
    ],
];
