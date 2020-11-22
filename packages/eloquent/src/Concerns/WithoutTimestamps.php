<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Concerns;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait WithoutTimestamps {
    /**
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function usesTimestamps() {
        return false;
    }
}
