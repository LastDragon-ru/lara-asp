<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeExec;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters as InstructionParameters;

class Parameters implements InstructionParameters {
    public function __construct(
        /**
         * Path to the executable.
         */
        public readonly string $target,
    ) {
        // empty
    }
}
