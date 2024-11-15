<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy;

use LastDragon_ru\LaraASP\Core\Application\Configuration\Configuration;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Direction;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Nulls;

class Config extends Configuration {
    public function __construct(
        /**
         * You can redefine operators for exiting (=default) types OR define own
         * types here. Note that directives are the recommended way and have
         * priority over the array. Please see the documentation for more
         * details.
         *
         * @var array<string, list<string|class-string<Operator>>>
         */
        public array $operators = [],
        /**
         * Determines how the `NULL` values should be treatment. By default,
         * there is no any processing, so the order of `NULL` depends on the
         * database. It may be set for all (if single value) or for each
         * direction (if array). Not all databases/builders may be supported.
         * Please check the documentation for more details.
         *
         * @var Nulls|non-empty-array<value-of<Direction>, Nulls>|null
         */
        public Nulls|array|null $nulls = null,
    ) {
        parent::__construct();
    }
}
