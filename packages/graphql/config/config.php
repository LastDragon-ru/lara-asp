<?php declare(strict_types = 1);

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;

/**
 * -----------------------------------------------------------------------------
 * GraphQL Settings
 * -----------------------------------------------------------------------------
 *
 * @var array{
 *      search_by: array{
 *          operators: array<string, list<string|class-string<Operator>>>,
 *      },
 *      sort_by: array{
 *          operators: array<string, list<string|class-string<Operator>>>,
 *      },
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
         * You can redefine operators for exiting (=default) types OR define own
         * types here. Note that directives is the recommended way and have
         * priority over the array. Please see the documentation for more
         * details.
         *
         * @see ../README.md#type-operators
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
         * You can redefine operators for exiting (=default) types OR define own
         * types here. Note that directives is the recommended way and have
         * priority over the array. Please see the documentation for more
         * details.
         *
         * @see ../README.md#operators-1
         */
        'operators' => [
            // empty
        ],
    ],
];

return $settings;
