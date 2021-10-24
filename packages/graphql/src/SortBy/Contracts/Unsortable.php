<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts;

use Nuwave\Lighthouse\Support\Contracts\Directive;

/**
 * Marks that property should be excluded from sort.
 */
interface Unsortable extends Directive {
    // empty
}
