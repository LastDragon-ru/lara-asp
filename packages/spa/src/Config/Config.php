<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Config;

use LastDragon_ru\LaraASP\Core\Application\Configuration\Configuration;

class Config extends Configuration {
    public function __construct(
        /**
         * Routes settings.
         */
        public Routes $routes = new Routes(),
        /**
         * SPA Settings.
         *
         * You can define settings that should be available for SPA.
         *
         * @var array<string, mixed>
         */
        public array $spa = [],
    ) {
        parent::__construct();
    }
}
