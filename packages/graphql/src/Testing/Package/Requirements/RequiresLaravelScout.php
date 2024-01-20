<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package\Requirements;

use Attribute;
use LastDragon_ru\LaraASP\Testing\Requirements\Requirements\RequiresComposerPackage;

/**
 * @internal
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class RequiresLaravelScout extends RequiresComposerPackage {
    public function __construct(?string $version = null) {
        parent::__construct('laravel/scout', $version);
    }
}
