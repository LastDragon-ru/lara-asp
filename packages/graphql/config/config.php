<?php declare(strict_types = 1);

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;

/**
 * -----------------------------------------------------------------------------
 * GraphQL Settings
 * -----------------------------------------------------------------------------
 *
 * @var array{
 *      search_by: array{
 *          operators: array<string, array<string|class-string<Operator>>>
 *      },
 *      sort_by: array{
 *          operators: array<string, array<string|class-string<Operator>>>
 *      }
 *      } $settings
 */
$settings = [
    /**
     * Settings for {@see \LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByDirective @searchBy} directive.
     */
    'search_by' => [
        /**
         * Operators
         * ---------------------------------------------------------------------
         *
         * You can (re)define types and supported operators here.
         *
         * @see Operator
         */
        'operators' => [
            // empty
        ],
    ],

    /**
     * Settings for {@see \LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByDirective @sortBy} directive.
     */
    'sort_by'   => [
        /**
         * Operators
         * ---------------------------------------------------------------------
         *
         * You can (re)define types and supported operators here.
         *
         * @see Operator
         */
        'operators' => [
            // empty
        ],
    ],
];

return $settings;
