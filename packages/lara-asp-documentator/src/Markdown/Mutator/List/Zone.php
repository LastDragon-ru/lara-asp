<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\List;

use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Delete;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Replace;

/**
 * @internal
 */
class Zone {
    public function __construct(
        public Location $location,
        /**
         * @var list<Replace|Delete>
         */
        public array $mutagens = [],
    ) {
        // empty
    }
}
