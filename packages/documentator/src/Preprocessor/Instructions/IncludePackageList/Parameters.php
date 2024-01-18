<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludePackageList;

use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;

class Parameters implements Serializable {
    public function __construct(
        public readonly ?string $template = null,
    ) {
        // empty
    }
}
