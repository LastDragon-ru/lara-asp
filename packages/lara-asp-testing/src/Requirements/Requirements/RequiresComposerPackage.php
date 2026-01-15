<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Requirements\Requirements;

use Attribute;
use LastDragon_ru\LaraASP\Testing\Requirements\PhpUnit\Extension;
use LastDragon_ru\PhpUnit\Extensions\Requirements\Attributes\RequiresPackage;

/**
 * @deprecated %{VERSION} Please use `\LastDragon_ru\PhpUnit\Extensions\Requirements\Extension` instead.
 *
 * @see Extension
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class RequiresComposerPackage extends RequiresPackage {
    // empty
}
