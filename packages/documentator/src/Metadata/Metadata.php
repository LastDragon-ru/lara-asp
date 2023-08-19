<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Metadata;

use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;

/**
 * @internal
 */
class Metadata implements Serializable {
    /**
     * @param array<string, array<string, list<string>>> $requirements
     */
    public function __construct(
        public string $version = '0.0.0',
        public array $requirements = [],
    ) {
        // empty
    }
}
