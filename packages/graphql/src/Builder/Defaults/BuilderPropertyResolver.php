<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Defaults;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\BuilderPropertyResolver as BuilderPropertyResolverContract;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use Override;

use function implode;

/**
 * @internal
 */
final class BuilderPropertyResolver implements BuilderPropertyResolverContract {
    public function __construct() {
        // empty
    }

    #[Override]
    public function getProperty(object $builder, Property $property): string {
        return implode('.', $property->getPath());
    }
}
