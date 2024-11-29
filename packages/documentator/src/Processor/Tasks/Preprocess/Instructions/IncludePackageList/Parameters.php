<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludePackageList;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters as ParametersContract;
use LastDragon_ru\LaraASP\Documentator\Utils\SortOrder;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;

/**
 * @deprecated %{VERSION}
 */
class Parameters implements ParametersContract, Serializable {
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
