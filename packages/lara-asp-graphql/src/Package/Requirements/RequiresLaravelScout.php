<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Package\Requirements;

use Attribute;
use LastDragon_ru\PhpUnit\Extensions\Requirements\Attributes\RequiresPackage;

/**
 * @internal
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class RequiresLaravelScout extends RequiresPackage {
    public function __construct(?string $version = null) {
        parent::__construct('laravel/scout', $version);
    }
}
