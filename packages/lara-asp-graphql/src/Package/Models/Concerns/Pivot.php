<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Package\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\Pivot as EloquentPivot;

/**
 * @internal
 */
abstract class Pivot extends EloquentPivot {
    use Concerns;
}
