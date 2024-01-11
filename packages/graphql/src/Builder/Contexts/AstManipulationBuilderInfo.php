<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Contexts;

use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;

class AstManipulationBuilderInfo {
    public function __construct(
        public readonly BuilderInfo $value,
    ) {
        // empty
    }
}
