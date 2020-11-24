<?php declare(strict_types = 1);
/**
 * Mixins for Laravel's classes.
 */

namespace LastDragon_ru\LaraASP\Testing;

use Illuminate\Testing\TestResponse;
use LastDragon_ru\LaraASP\Testing\Mixins\TestResponseMixin;
use function class_exists;

if (class_exists(TestResponse::class)) {
    TestResponse::mixin(new TestResponseMixin());
}
