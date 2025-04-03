<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludePackageList;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters as InstructionParameters;
use LastDragon_ru\LaraASP\Documentator\Utils\SortOrder;

/**
 * @deprecated 8.0.0
 */
readonly class Parameters implements InstructionParameters {
    public function __construct(
        /**
         * Directory path.
         */
        public string $target,
        /**
         * Blade template.
         */
        public string $template = 'default',
        /**
         * Sort order.
         */
        public SortOrder $order = SortOrder::Asc,
    ) {
        // empty
    }
}
