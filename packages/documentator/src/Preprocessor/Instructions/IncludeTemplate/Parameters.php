<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeTemplate;

use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;

class Parameters implements Serializable {
    public function __construct(
        /**
         * Array of variables (`${name}`) to replace.
         *
         * @var array<string, scalar|null>
         */
        public readonly array $data,
    ) {
        // empty
    }
}
