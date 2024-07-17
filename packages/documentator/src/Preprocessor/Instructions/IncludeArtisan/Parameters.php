<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeArtisan;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Parameters as ParametersContract;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;

class Parameters implements ParametersContract, Serializable {
    public function __construct(
        /**
         * Artisan command. The following special variables supported:
         *
         *  * `{$directory}` - path of the directory where the file is located.
         *  * `{$file}` - path of the file.
         */
        public readonly string $target,
    ) {
        // empty
    }
}
