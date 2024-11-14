<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Formats\Filesize;

use LastDragon_ru\LaraASP\Core\Application\Configuration\Configuration;

class FilesizeOptions extends Configuration {
    public function __construct(
        public ?int $base = null,
        /**
         * Units (names/translations). By default, the package namespace is
         * used. Specify the namespace for custom strings (eg `*::<string>`).
         * The last string will be used as a default value if no translation.
         *
         * @var non-empty-list<non-empty-list<string>>|null
         */
        public ?array $units = null,
    ) {
        parent::__construct();
    }
}
