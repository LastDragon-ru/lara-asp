<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder;

use LastDragon_ru\LaraASP\GraphQL\Package;

use function trigger_deprecation;

// phpcs:disable PSR1.Files.SideEffects

trigger_deprecation(Package::Name, '5.5.0', 'Please use `%s` instead.', Field::class);

/**
 * @deprecated 5.5.0 Please use {@see Field} instead.
 */
class Property extends Field {
    // empty
}
