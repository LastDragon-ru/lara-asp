<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Config;

use LastDragon_ru\LaraASP\Core\Application\Configuration\Configuration;
use LastDragon_ru\LaraASP\Formatter\Formats\IntlNumber\IntlNumberOptions;

class Intl extends Configuration {
    public function __construct(
        public ?IntlNumberOptions $number,
    ) {
        parent::__construct();
    }
}
