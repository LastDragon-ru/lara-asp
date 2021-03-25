<?php declare(strict_types = 1);

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
        'scalars' => [
            // empty
        ],

        /**
         * Scalar aliases
         * ---------------------------------------------------------------------
         *
         * Allow redefine scalar type in conditions.
         *
         * @var array<string, string>
         */
        'aliases' => [
            // empty
        ],
    ],
];
