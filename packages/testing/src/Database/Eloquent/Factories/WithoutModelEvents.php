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
     * @inheritDoc
     */
    public function make($attributes = [], ?Model $parent = null) {
        return $this->callWithoutModelEvents(function () use ($attributes, $parent) {
            return parent::make($attributes, $parent);
        });
    }

    /**
     * @inheritDoc
     */
    public function create($attributes = [], ?Model $parent = null) {
        return $this->callWithoutModelEvents(function () use ($attributes, $parent) {
            return parent::create($attributes, $parent);
        });
    }

    /**
     * @template T
     *
     * @param Closure(): T $closure
     *
     * @return T
     */
    private function callWithoutModelEvents(Closure $closure): mixed {
        return $this->modelName()::withoutEvents($closure);
    }
}
