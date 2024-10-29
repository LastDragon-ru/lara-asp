<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Config\Formats;

use LastDragon_ru\LaraASP\Core\Application\Configuration\Configuration;
use LastDragon_ru\LaraASP\Formatter\Utils\DurationFormatter;

/**
 * @see DurationFormatter
 */
class DurationFormatPattern extends Configuration {
    public function __construct(
        public string $pattern,
    ) {
        parent::__construct();
    }
}
