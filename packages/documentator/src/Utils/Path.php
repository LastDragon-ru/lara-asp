<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use LastDragon_ru\LaraASP\Core\Utils\Path as CorePath;
use LastDragon_ru\LaraASP\Documentator\Package;

use function trigger_deprecation;

// phpcs:disable PSR1.Files.SideEffects

trigger_deprecation(Package::Name, '%{VERSION}', 'Please use `%s` instead.', CorePath::class);

/**
 * @deprecated %{VERSION} Use {@see CorePath} instead.
 */
class Path extends CorePath {
    // empty
}
