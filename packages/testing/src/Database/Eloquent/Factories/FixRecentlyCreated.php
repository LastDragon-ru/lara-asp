<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Database\Eloquent\Factories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * After creating the model will have `wasRecentlyCreated = true`, in most
 * cases this is unwanted behavior, this trait fixes it.
 *
 * @mixin \Illuminate\Database\Eloquent\Factories\Factory
 */
trait FixRecentlyCreated {
    /**
     * @inheritdoc
     */
    protected function callAfterCreating(Collection $instances, ?Model $parent = null) {
        $this->fixRecentlyCreated($instances);
        parent::callAfterCreating($instances, $parent);
    }

    private function fixRecentlyCreated(Collection $instances): void {
        foreach ($instances as $instance) {
            $instance->wasRecentlyCreated = false;
        }
    }
}
