<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Defaults;

use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\BuilderPropertyResolver as BuilderPropertyResolverContract;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scout\FieldResolver as ScoutFieldResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use Override;

use function implode;

/**
 * @internal
 */
final class BuilderPropertyResolver implements BuilderPropertyResolverContract {
    public function __construct(
        private readonly ?ScoutFieldResolver $resolver = null,
    ) {
        // empty
    }

    #[Override]
    public function getProperty(object $builder, Property $property): string {
        return $builder instanceof ScoutBuilder && $this->resolver
            ? $this->resolver->getField($builder->model, $property)
            : implode('.', $property->getPath());
    }
}
