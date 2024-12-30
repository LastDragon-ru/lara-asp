<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeGraphqlDirective;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters as InstructionParameters;

class Parameters implements InstructionParameters {
    public function __construct(
        /**
         * Directive name (started with `@` sign)
         */
        public readonly string $target,
    ) {
        // empty
    }
}
