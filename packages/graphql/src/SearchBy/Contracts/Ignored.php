<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts;

use Nuwave\Lighthouse\Support\Contracts\Directive;

/**
 * Marks that field should be excluded from search.
 */
interface Ignored extends Directive {
    // empty
}
