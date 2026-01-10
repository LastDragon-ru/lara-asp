<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Context;

use LastDragon_ru\LaraASP\GraphQL\Builder\Operators;

readonly class HandlerContextOperators {
    public function __construct(
        public Operators $value,
    ) {
        // empty
    }
}
