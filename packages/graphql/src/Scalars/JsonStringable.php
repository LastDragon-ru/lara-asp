<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Scalars;

use Stringable;

/**
 * Marks that string representation of the class is already a valid JSON string,
 * so validation can be omitted.
 */
interface JsonStringable extends Stringable {
    // empty
}
