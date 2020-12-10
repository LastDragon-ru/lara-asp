<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Concerns;

use Illuminate\Foundation\Bus\PendingDispatch;

trait Dispatchable {
    use WithInitialization;

    public function dispatch(): PendingDispatch {
        return $this->ifInitialized(function () {
            return new PendingDispatch($this);
        });
    }
}
