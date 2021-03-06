<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Concerns;

/**
 * @see https://github.com/laravel/framework/issues/26877
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait WithoutQueueableRelations {
    /**
     * @inheritdoc
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function getQueueableRelations() {
        // Usually, there are no reasons to save relations while serialization
        // of `Queueable`, also, relations may create circular dependency, and
        // deserialization will be failed.
        return [];
    }
}
