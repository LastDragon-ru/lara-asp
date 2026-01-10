<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Context;

readonly class HandlerContextImplicit {
    public function __construct(
        public bool $value,
    ) {
        // empty
    }
}
