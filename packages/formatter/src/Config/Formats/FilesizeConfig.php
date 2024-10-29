<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Config\Formats;

use LastDragon_ru\LaraASP\Core\Application\Configuration\Configuration;

class FilesizeConfig extends Configuration {
    public function __construct(
        /**
         * @var array<non-empty-string, FilesizeFormat>
         */
        public array $formats = [],
    ) {
        parent::__construct();
    }
}
