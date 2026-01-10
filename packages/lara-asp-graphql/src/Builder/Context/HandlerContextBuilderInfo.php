<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Context;

use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;

readonly class HandlerContextBuilderInfo {
    public function __construct(
        public BuilderInfo $value,
    ) {
        // empty
    }
}
