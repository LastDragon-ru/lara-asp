<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Database\Eloquent\Factories;

use Illuminate\Database\Eloquent\Model;

/**
 * After creating the model will have `wasRecentlyCreated = true`, in most
 * cases this is unwanted behavior, this trait fixes it.
 *
 * @mixin \Illuminate\Database\Eloquent\Factories\Factory
 */
trait FixRecentlyCreated {
    /**
     * @inheritdoc
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function configure() {
        return parent::configure()->afterCreating(function (Model $model) {
            $model->wasRecentlyCreated = false;
        });
    }
}
