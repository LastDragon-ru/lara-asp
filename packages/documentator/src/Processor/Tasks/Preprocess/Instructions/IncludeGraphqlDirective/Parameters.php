<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeGraphqlDirective;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters as InstructionParameters;

readonly class Parameters implements InstructionParameters {
    public function __construct(
        /**
         * Directive name (started with `@` sign)
         *
         * @var non-empty-string
         */
        public string $target,
    ) {
        // empty
    }
}
