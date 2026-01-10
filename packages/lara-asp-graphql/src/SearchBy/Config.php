<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy;

use LastDragon_ru\LaraASP\Core\Application\Configuration\Configuration;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;

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
    ) {
        parent::__construct();
    }
}
