<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Config;

use LastDragon_ru\LaraASP\Core\Application\Configuration\Configuration;
use LastDragon_ru\LaraASP\Formatter\Contracts\Format as FormatContract;

/**
 * @template TOptions of Configuration|null
 * @template TValue
 */
class Format extends Configuration {
    public function __construct(
        /**
         * @var class-string<FormatContract<TOptions, TValue>>
         */
        public string $class,
        /**
         * @var TOptions
         */
        public ?Configuration $default = null,
        /**
         * @var array<string, TOptions>
         */
        public array $locales = [],
    ) {
        parent::__construct();
    }
}
