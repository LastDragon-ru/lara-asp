<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\Concerns;

use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * @internal
 */
abstract class Model extends EloquentModel {
    use Concerns;
}
