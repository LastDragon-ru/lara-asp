<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Concerns;

trait WithoutTimestamps {
    public function usesTimestamps() {
        return false;
    }
}
