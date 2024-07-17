<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeGraphqlDirective;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Parameters as ParametersContract;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;

class Parameters implements ParametersContract, Serializable {
    public function __construct(
        /**
         * Directive name (started with `@` sign)
         */
        public readonly string $target,
    ) {
        // empty
    }
}
