<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Ignored as IgnoredContract;

/**
 * Marks that field/definition should be excluded from search.
 */
interface Ignored extends IgnoredContract, Scope {
    // empty
}
