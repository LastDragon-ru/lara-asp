<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Config;

use LastDragon_ru\LaraASP\Core\Application\Configuration\Configuration;

class Routes extends Configuration {
    public function __construct(
        public bool $enabled = false,
        public string $middleware = 'web',
        public ?string $prefix = null,
    ) {
        parent::__construct();
    }
}
