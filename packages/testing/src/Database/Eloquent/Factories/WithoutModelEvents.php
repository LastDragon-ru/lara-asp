<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Database\Eloquent\Factories;

use Closure;
use Illuminate\Database\Eloquent\Model;

/**
 * Disable models events during make/create.
 *
 * @mixin \Illuminate\Database\Eloquent\Factories\Factory
 */
trait WithoutModelEvents {
    /**
     * @inheritdoc
     */
    public function make($attributes = [], ?Model $parent = null) {
        return $this->callWithoutModelEvents(function () use ($attributes, $parent) {
            return parent::make($attributes, $parent);
        });
    }

    /**
     * @inheritdoc
     */
    public function create($attributes = [], ?Model $parent = null) {
        return $this->callWithoutModelEvents(function () use ($attributes, $parent) {
            return parent::create($attributes, $parent);
        });
    }

    private function callWithoutModelEvents(Closure $closure): mixed {
        return $this->modelName()::withoutEvents($closure);
    }
}
