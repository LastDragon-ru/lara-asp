<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Config;

use LastDragon_ru\LaraASP\Core\Application\Configuration\Configuration;

class Sort extends Configuration {
    public function __construct(
        /**
         * @var non-empty-string
         */
        public string $name = 'order',
        public bool $enabled = true,
    ) {
        parent::__construct();
    }
}
