<?php declare(strict_types = 1);
/**
 * Mixins for Laravel's classes.
 */

namespace LastDragon_ru\LaraASP\Core;

use Illuminate\Routing\Route;
use LastDragon_ru\LaraASP\Core\Mixins\RouteMixin;
use function class_exists;

if (class_exists(Route::class)) {
    Route::mixin(new RouteMixin());
}
