<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Scout;

use Illuminate\Database\Eloquent\Model;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scout\FieldResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;

use function implode;

/**
 * @internal
 */
class DefaultFieldResolver implements FieldResolver {
    public function __construct() {
        // empty
    }

    public function getField(Model $model, Property $property): string {
        return implode('.', $property->getPath());
    }
}
