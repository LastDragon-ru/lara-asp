<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\List;

use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Delete;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Replace;

/**
 * @internal
 */
class ZoneDefault {
    public function __construct(
        /**
         * @var list<Replace|Delete>
         */
        public array $mutagens = [],
    ) {
        // empty
    }
}
