<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocBlock;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters as ParametersContract;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;

class Parameters implements ParametersContract, Serializable {
    public function __construct(
        /**
         * File path.
         */
        public readonly string $target,
        /**
         * Include the class summary?
         */
        public readonly bool $summary = true,
        /**
         * Include the class description?
         */
        public readonly bool $description = true,
    ) {
        // empty
    }
}
