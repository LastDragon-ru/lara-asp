<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Mixins;

/**
 * Eloquent builder mixin. We use `extends` here because if macro return value
 * we should register it in {@link \Illuminate\Database\Query\Builder} and
 * {@link \Illuminate\Database\Eloquent\Builder}.
 */
class EloquentBuilderMixin extends QueryBuilderMixin {
    // empty
}
