<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions;

use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;

class IncludeTemplateParameters implements Serializable {
    public function __construct(
        /**
         * @var array<string, scalar|null>
         */
        public readonly array $data = [],
    ) {
        // empty
    }
}
