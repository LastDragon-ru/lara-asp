<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Config;

use LastDragon_ru\LaraASP\Core\Application\Configuration\Configuration;

class Search extends Configuration {
    public function __construct(
        /**
         * @var non-empty-string
         */
        public string $name = 'where',
        public bool $enabled = true,
    ) {
        parent::__construct();
    }
}
