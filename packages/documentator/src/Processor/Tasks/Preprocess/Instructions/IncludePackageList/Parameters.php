<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludePackageList;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters as InstructionParameters;
use LastDragon_ru\LaraASP\Documentator\Utils\SortOrder;

/**
 * @deprecated %{VERSION}
 */
class Parameters implements InstructionParameters {
    public function __construct(
        /**
         * Directory path.
         */
        public readonly string $target,
        /**
         * Blade template.
         */
        public readonly string $template = 'default',
        /**
         * Sort order.
         */
        public readonly SortOrder $order = SortOrder::Asc,
    ) {
        // empty
    }
}
