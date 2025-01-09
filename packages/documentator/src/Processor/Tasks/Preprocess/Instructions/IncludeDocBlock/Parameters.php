<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocBlock;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters as InstructionParameters;

readonly class Parameters implements InstructionParameters {
    public function __construct(
        /**
         * File path.
         */
        public string $target,
        /**
         * Include the class summary?
         */
        public bool $summary = true,
        /**
         * Include the class description?
         */
        public bool $description = true,
    ) {
        // empty
    }
}
