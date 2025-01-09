<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Operators;

use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Nulls;

readonly class SortContextNulls {
    public function __construct(
        public ?Nulls $value,
    ) {
        // empty
    }
}
