<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\Pivot as EloquentPivot;

abstract class Pivot extends EloquentPivot {
    use Concerns;
}
