<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Mixins;

use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * Eloquent builder mixin. We use `extends` here because if macro return value
 * we should register it in {@link \Illuminate\Database\Query\Builder} and
 * {@link \Illuminate\Database\Eloquent\Builder}.
 */
class EloquentBuilderMixin extends QueryBuilderMixin {
    public function orderByKey(): Closure {
        return function (string $direction = 'asc'): Builder {
            /** @var Builder $this */
            return $this->orderBy($this->getDefaultKeyName(), $direction);
        };
    }

    public function orderByKeyDesc(): Closure {
        return function (): Builder {
            /** @var Builder $this */
            return $this->orderByKey('desc');
        };
    }
}
