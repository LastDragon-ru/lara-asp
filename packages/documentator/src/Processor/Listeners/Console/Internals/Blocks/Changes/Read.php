<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Internals\Blocks\Changes;

use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Enums\Flag;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Enums\Mark;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Internals\Blocks\Change;

/**
 * @internal
 */
class Read extends Change {
    public function __construct(float $start, Mark $mark, string $path) {
        parent::__construct($start, Flag::Read, $mark, $path);
    }
}
