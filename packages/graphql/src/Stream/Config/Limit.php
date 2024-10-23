<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Config;

use LastDragon_ru\LaraASP\Core\Application\Configuration\Configuration;

class Limit extends Configuration {
    public function __construct(
        /**
         * @var non-empty-string
         */
        public string $name = 'limit',
        /**
         * @var int<1, max>
         */
        public int $default = 25,
        /**
         * @var int<1, max>
         */
        public int $max = 100,
    ) {
        parent::__construct();
    }
}
