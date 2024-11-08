<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Formatters\DateTime;

use IntlDateFormatter;
use LastDragon_ru\LaraASP\Core\Application\Configuration\Configuration;

/**
 * @see IntlDateFormatter
 */
class Options extends Configuration {
    public function __construct(
        /**
         * @var IntlDateFormatter::*
         */
        public ?int $dateType = null,
        /**
         * @var IntlDateFormatter::*
         */
        public ?int $timeType = null,
        /**
         * @see IntlDateFormatter::setPattern()
         */
        public ?string $pattern = null,
    ) {
        parent::__construct();
    }
}
