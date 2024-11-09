<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Formatters\Duration;

use LastDragon_ru\LaraASP\Core\Application\Configuration\Configuration;

/**
 * @see PatternFormatter
 */
class PatternOptions extends Configuration {
    public function __construct(
        public string $pattern,
    ) {
        parent::__construct();
    }
}
