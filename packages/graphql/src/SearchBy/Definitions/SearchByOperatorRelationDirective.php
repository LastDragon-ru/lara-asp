<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions;

use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Complex\Relationship;

/**
 * @deprecated 5.6.0 Please use {@see SearchByOperatorRelationshipDirective}.
 */
class SearchByOperatorRelationDirective extends Relationship {
    // Lighthouse loads all classes from directive namespace this leads to
    // 'Class "Orchestra\Testbench\TestCase" not found' error for our *Test
    // classes. This class required to avoid this error.
}
