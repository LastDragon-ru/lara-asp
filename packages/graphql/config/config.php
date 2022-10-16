<?php declare(strict_types = 1);

use LastDragon_ru\LaraASP\Core\Enum as CoreEnum;
use LastDragon_ru\LaraASP\Eloquent\Enum as EloquentEnum;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator;

/**
 * -----------------------------------------------------------------------------
 * GraphQL Settings
 * -----------------------------------------------------------------------------
 *
 * @var array{
 *      search_by: array{
 *          operators: array<string, array<string|class-string<Operator>>>
 *      },
 *      enums: array<class-string<CoreEnum>>
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
     * These enums will be registered automatically. You can use key to specify
     * enum name.
     *
     * @deprecated Consider using native PHP enums.
     *
     * @see CoreEnum
     * @see EloquentEnum
     */
    'enums'     => [
        // empty,
    ],
];

return $settings;
