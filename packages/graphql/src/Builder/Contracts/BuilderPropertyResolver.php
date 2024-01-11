<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Contracts;

use LastDragon_ru\LaraASP\GraphQL\Builder\Property;

/**
 * Convert {@see Property} into builder property.
 */
interface BuilderPropertyResolver {
    public function getProperty(object $builder, Property $property): string;
}
