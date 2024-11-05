<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Config\Formats;

use LastDragon_ru\LaraASP\Core\Application\Configuration\Configuration;
use LastDragon_ru\LaraASP\Formatter\Formatter;

class FilesizeFormat extends Configuration {
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
        /**
         * The number format name that will be used if the display value is
         * integer (no fraction part). Default is {@see Formatter::Integer}.
         *
         * @see Formatter::Integer
         */
        public ?string $integerFormat = null,
        /**
         * The number format name that will be used if the display value is
         * float. Default is {@see Formatter::Decimal}.
         *
         * @see Formatter::Decimal
         */
        public ?string $decimalFormat = null,
    ) {
        parent::__construct();
    }
}