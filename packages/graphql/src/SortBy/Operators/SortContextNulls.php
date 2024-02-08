<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Operators;

use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Nulls;

class SortContextNulls {
    public function __construct(
        public readonly ?Nulls $value,
    ) {
        // empty
    }
}
