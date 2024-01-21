<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Directives;

use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;

class HandlerContextBuilderInfo {
    public function __construct(
        public readonly BuilderInfo $value,
    ) {
        // empty
    }
}
