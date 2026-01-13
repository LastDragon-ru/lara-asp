<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Requirements\PhpUnit;

use LastDragon_ru\LaraASP\Testing\Requirements\Requirement;
use LastDragon_ru\PhpUnit\Extensions\Requirements\Extension as NewExtension;

/**
 * Marks test skipped if requirements don't meet.
 *
 * @deprecated %{VERSION} Please use `\LastDragon_ru\PhpUnit\Extensions\Requirements\Extension` instead.
 *
 * @see Requirement
 */
class Extension extends NewExtension {
    // empty
}
