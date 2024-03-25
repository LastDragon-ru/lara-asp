<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives;

use LastDragon_ru\LaraASP\GraphQL\Builder\Directives\OperatorsDirective;
use LastDragon_ru\LaraASP\GraphQL\Package;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Scope;

use function trigger_deprecation;

// phpcs:disable PSR1.Files.SideEffects

trigger_deprecation(Package::Name, '5.6.0', 'Please use `%s` instead.', ExtendOperators::class);

/**
 * @deprecated 5.6.0 Use {@see ExtendOperators} instead.
 */
class Operators extends OperatorsDirective implements Scope {
    // empty
}
