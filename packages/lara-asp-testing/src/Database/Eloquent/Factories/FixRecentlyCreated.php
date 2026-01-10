<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Database\Eloquent\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Override;

/**
 * After creating the model will have `wasRecentlyCreated = true`, in most
 * cases this is unwanted behavior, this trait fixes it.
 *
 * @phpstan-require-extends Factory
 */
trait FixRecentlyCreated {
    /**
     * @inheritDoc
     *
     * @param Collection<array-key,Model> $instances
     */
    #[Override]
    protected function callAfterCreating(Collection $instances, ?Model $parent = null) {
        $this->fixRecentlyCreated($instances);
        parent::callAfterCreating($instances, $parent);
    }

    /**
     * @param Collection<array-key,Model> $instances
     */
    private function fixRecentlyCreated(Collection $instances): void {
        foreach ($instances as $instance) {
            $instance->wasRecentlyCreated = false;
        }
    }
}
