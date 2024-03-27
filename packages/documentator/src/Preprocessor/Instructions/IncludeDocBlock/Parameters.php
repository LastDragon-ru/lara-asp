<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeDocBlock;

use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;

class Parameters implements Serializable {
    public function __construct(
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
