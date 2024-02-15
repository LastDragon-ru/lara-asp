<?php declare(strict_types = 1);

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Direction;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Nulls;
use Nuwave\Lighthouse\Schema\Directives\RenameDirective;

/**
 * -----------------------------------------------------------------------------
 * GraphQL Settings
 * -----------------------------------------------------------------------------
 *
 * Note: You need to clear/rebuild the cached schema and IDE helper files after
 * changing any of the settings.
 *
 * @see https://lighthouse-php.com/master/api-reference/commands.html#clear-cache
 * @see https://lighthouse-php.com/master/api-reference/commands.html#ide-helper
 *
 * @var array{
 *      search_by: array{
 *          operators: array<string, list<string|class-string<Operator>>>,
 *      },
 *      sort_by: array{
 *          operators: array<string, list<string|class-string<Operator>>>,
 *          nulls: Nulls|non-empty-array<value-of<Direction>, Nulls>|null,
 *      },
 *      stream: array{
 *          search: array{
 *              name: string,
 *              enabled: bool,
 *          },
 *          sort: array{
 *              name: string,
 *              enabled: bool,
 *          },
 *          limit: array{
 *              name: string,
 *              default: int<1, max>,
 *              max: int<1, max>,
 *          },
 *          offset: array{
 *              name: string,
 *          }
 *      },
 *      builder: array{
 *          allowed_directives: list<class-string>,
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
         */
        'operators' => [
            // empty
        ],

        /**
         * NULLs
         *
         * ---------------------------------------------------------------------
         *
         * Determines how the `NULL` values should be treatment. By default,
         * there is no any processing, so the order of `NULL` depends on the
         * database. It may be set for all (if single value) or for each
         * direction (if array). Not all databases/builders may be supported.
         * Please check the documentation for more details.
         *
         * @see Nulls
         */
        'nulls'     => null,
    ],

    /**
     * Settings for {@see \LastDragon_ru\LaraASP\GraphQL\Stream\Definitions\StreamDirective @stream} directive.
     */
    'stream'    => [
        'search' => [
            'name'    => 'where',
            'enabled' => true,
        ],
        'sort'   => [
            'name'    => 'order',
            'enabled' => true,
        ],
        'limit'  => [
            'name'    => 'limit',
            'default' => 25,
            'max'     => 100,
        ],
        'offset' => [
            'name' => 'offset',
        ],
    ],

    /**
     * General settings for all `Builder` directives like `@searchBy`/`@sortBy`/etc.
     */
    'builder'   => [
        /**
         * The list of the directives which should be copied from the original
         * field into the generated `input` field. All other directives except
         * {@see Operator} will be ignored.
         *
         * The `instanceof` operator is used to check.
         */
        'allowed_directives' => [
            RenameDirective::class,
        ],
    ],
];

return $settings;
