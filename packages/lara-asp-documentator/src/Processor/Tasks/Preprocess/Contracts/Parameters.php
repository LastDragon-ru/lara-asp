<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts;

use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;

/**
 * @property-read non-empty-string $target
 */
interface Parameters extends Serializable {
    // empty
}
