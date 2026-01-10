<?php declare(strict_types = 1);

/**
 * Mixins for Laravel's classes.
 */

namespace LastDragon_ru\LaraASP\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use LastDragon_ru\LaraASP\Eloquent\Mixins\EloquentBuilderMixin;

use function class_exists;

if (class_exists(Builder::class)) {
    Builder::mixin(new EloquentBuilderMixin());
}
