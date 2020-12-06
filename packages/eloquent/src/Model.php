<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use LastDragon_ru\LaraASP\Eloquent\Concerns\SaveOrThrow;
use LastDragon_ru\LaraASP\Eloquent\Concerns\WithoutQueueableRelations;

abstract class Model extends EloquentModel {
    use SaveOrThrow, WithoutQueueableRelations;
}
