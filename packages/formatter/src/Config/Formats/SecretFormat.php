<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Config\Formats;

use LastDragon_ru\LaraASP\Core\Application\Configuration\Configuration;

class SecretFormat extends Configuration {
    public function __construct(
        /**
         * Number of how many characters should be shown.
         *
         * @var int<0, max>
         */
        public int $visible,
    ) {
        parent::__construct();
    }
}
