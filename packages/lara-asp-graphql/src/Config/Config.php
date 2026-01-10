<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Config;

use LastDragon_ru\LaraASP\Core\Application\Configuration\Configuration;
use LastDragon_ru\LaraASP\GraphQL\Builder\Config as BuilderConfig;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Config as SearchByConfig;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Config as SortByConfig;
use LastDragon_ru\LaraASP\GraphQL\Stream\Config as StreamConfig;

class Config extends Configuration {
    public function __construct(
        /**
         * Settings for {@see \LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByDirective @searchBy} directive.
         */
        public SearchByConfig $searchBy = new SearchByConfig(),
        /**
         * Settings for {@see \LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByDirective @sortBy} directive.
         */
        public SortByConfig $sortBy = new SortByConfig(),
        /**
         * Settings for {@see \LastDragon_ru\LaraASP\GraphQL\Stream\Definitions\StreamDirective @stream} directive.
         */
        public StreamConfig $stream = new StreamConfig(),
        /**
         * General settings for all `Builder` directives like `@searchBy`/`@sortBy`/etc.
         */
        public BuilderConfig $builder = new BuilderConfig(),
    ) {
        parent::__construct();
    }
}
