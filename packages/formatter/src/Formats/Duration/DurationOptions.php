<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Formats\Duration;

use LastDragon_ru\LaraASP\Core\Application\Configuration\Configuration;

class DurationOptions extends Configuration {
    public function __construct(
        public string $pattern,
    ) {
        parent::__construct();
    }
}
