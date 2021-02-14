<?php declare(strict_types = 1);

/**
 * Mixins for Laravel's classes.
 */

namespace LastDragon_ru\LaraASP\Eloquent;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\Eloquent\Mixins\EloquentBuilderMixin;
use LastDragon_ru\LaraASP\Eloquent\Mixins\QueryBuilderMixin;

use function class_exists;

if (class_exists(QueryBuilder::class)) {
    QueryBuilder::mixin(new QueryBuilderMixin());
}

if (class_exists(EloquentBuilder::class)) {
    EloquentBuilder::mixin(new EloquentBuilderMixin());
}
