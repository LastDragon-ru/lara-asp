<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions;

use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;

class IncludeDocBlockParameters implements Serializable {
    public function __construct(
        public readonly bool $summary = false,
        public readonly bool $description = true,
    ) {
        // empty
    }
}
