<?php declare(strict_types = 1);

use LastDragon_ru\LaraASP\Core\Utils\ConfigMerger;

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
         * Scalars
         * ---------------------------------------------------------------------
         *
         * You can (re)define scalars and supported operators here.
         *
         * @var array<string, array<class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator>>>
         */
        'scalars'   => [
            // This value has no effect inside the published config.
            ConfigMerger::Replace => true,
        ],

        /**
         * Complex operators.
         * ---------------------------------------------------------------------
         *
         * Allow define own complex operators here.
         *
         * @var array<class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\ComplexOperator>>
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
