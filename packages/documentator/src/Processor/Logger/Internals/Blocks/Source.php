<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals\Blocks;

use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals\Block;
use Override;

/**
 * @internal
 */
abstract class Source extends Block {
    #[Override]
    public function child(Block $block): bool {
        return $block instanceof Task
            || $block instanceof Change;
    }
}
