<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals\Blocks\Changes;

use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums\Flag;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums\Mark;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals\Blocks\ChangeBlock;

/**
 * @internal
 */
class WriteBlock extends ChangeBlock {
    public function __construct(float $start, Mark $mark, string $path) {
        parent::__construct($start, Flag::Write, $mark, $path);
    }
}
