<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeDocBlock;

use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;

class Parameters implements Serializable {
    public function __construct(
        public readonly bool $summary = false,
        public readonly bool $description = true,
    ) {
        // empty
    }
}
