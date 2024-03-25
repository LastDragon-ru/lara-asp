<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions;

use LastDragon_ru\LaraASP\GraphQL\Package;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Operators;

use function trigger_deprecation;

// phpcs:disable PSR1.Files.SideEffects

trigger_deprecation(Package::Name, '5.6.0', 'Please use `%s` instead.', SearchByExtendOperatorsDirective::class);

/**
 * @deprecated 5.6.0 Use {@see SearchByExtendOperatorsDirective} instead.
 */
class SearchByOperatorsDirective extends Operators {
    // Lighthouse loads all classes from directive namespace this leads to
    // 'Class "Orchestra\Testbench\TestCase" not found' error for our *Test
    // classes. This class required to avoid this error.
}
