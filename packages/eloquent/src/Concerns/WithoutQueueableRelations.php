<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * @see https://github.com/laravel/framework/issues/26877
 * @mixin Model
 */
trait WithoutQueueableRelations {
    /**
     * @return list<string>
     */
    public function getQueueableRelations(): array {
        // Usually, there are no reasons to save relations while serialization
        // of `Queueable`, also, relations may create circular dependency, and
        // deserialization will be failed.
        return [];
    }
}
