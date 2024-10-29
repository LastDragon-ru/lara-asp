<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Config;

use LastDragon_ru\LaraASP\Core\Application\Configuration\Configuration;
use LastDragon_ru\LaraASP\Formatter\Formatter;

/**
 * @see Formatter
 */
class Defaults extends Configuration {
    public function __construct(
        /**
         * Default currency.
         *
         * @var non-empty-string
         */
        public string $currency = 'USD',
    ) {
        parent::__construct();
    }
}
