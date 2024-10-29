<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Config;

use LastDragon_ru\LaraASP\Core\Application\Configuration\Configuration;
use NumberFormatter;

class IntlOptions extends Configuration {
    public function __construct(
        /**
         * @see NumberFormatter::setSymbol()
         *
         * @var array<int, string>
         */
        public array $symbols = [],
        /**
         * @see NumberFormatter::setAttribute()
         *
         * @var array<int, int|float>
         */
        public array $attributes = [],
        /**
         * @see NumberFormatter::setTextAttribute()
         *
         * @var array<int, string>
         */
        public array $textAttributes = [],
    ) {
        parent::__construct();
    }
}
