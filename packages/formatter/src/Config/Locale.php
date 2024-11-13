<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Config;

use LastDragon_ru\LaraASP\Core\Application\Configuration\Configuration;
use LastDragon_ru\LaraASP\Formatter\Config\Formats\DateTimeConfig;
use LastDragon_ru\LaraASP\Formatter\Config\Formats\FilesizeConfig;

class Locale extends Configuration {
    public function __construct(
        public DateTimeConfig $datetime = new DateTimeConfig(),
        public FilesizeConfig $filesize = new FilesizeConfig(),
    ) {
        parent::__construct();
    }
}
