<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scout;

use Illuminate\Database\Eloquent\Model;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\BuilderFieldResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;

/**
 * Convert nested property into Scout field.
 *
 * @deprecated 5.4.0 Please use {@see BuilderFieldResolver} instead.
 *
 * @see BuilderFieldResolver
 */
interface FieldResolver {
    public function getField(Model $model, Property $property): string;
}
