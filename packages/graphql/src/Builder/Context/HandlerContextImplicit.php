<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Context;

class HandlerContextImplicit {
    public function __construct(
        public readonly bool $value,
    ) {
        // empty
    }
}
