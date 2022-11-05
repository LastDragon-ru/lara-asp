<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scout;

use Illuminate\Database\Eloquent\Model;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;

/**
 * Convert nested property into Scout field.
 */
interface FieldResolver {
    public function getField(Model $model, Property $property): string;
}
