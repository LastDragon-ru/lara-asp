<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Ignored as IgnoredContract;

/**
 * Marks that field/definition should be excluded from sort.
 */
interface Ignored extends IgnoredContract, Scope {
    // empty
}
