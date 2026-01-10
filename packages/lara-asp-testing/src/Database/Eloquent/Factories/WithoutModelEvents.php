<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Database\Eloquent\Factories;

use Closure;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * Disable models events during make/create.
 *
 * @phpstan-require-extends Factory
 */
trait WithoutModelEvents {
    /**
     * @inheritDoc
     */
    #[Override]
    public function make($attributes = [], ?Model $parent = null) {
        return $this->callWithoutModelEvents(function () use ($attributes, $parent) {
            return parent::make($attributes, $parent);
        });
    }

    /**
     * @inheritDoc
     */
    #[Override]
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
