<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent;

use Illuminate\Database\Eloquent\Relations\Pivot as EloquentPivot;
use LastDragon_ru\LaraASP\Eloquent\Concerns\SaveOrThrow;
use LastDragon_ru\LaraASP\Eloquent\Concerns\WithoutQueueableRelations;

class Pivot extends EloquentPivot {
    use SaveOrThrow, WithoutQueueableRelations;
}
