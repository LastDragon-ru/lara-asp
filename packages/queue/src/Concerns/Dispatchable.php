<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Concerns;

use Illuminate\Foundation\Bus\PendingDispatch;

trait Dispatchable {
    public function dispatch(): PendingDispatch {
        return new PendingDispatch($this);
    }
}
