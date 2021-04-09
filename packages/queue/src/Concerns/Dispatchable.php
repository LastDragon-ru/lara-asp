<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Concerns;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Foundation\Bus\PendingDispatch;

use function app;

trait Dispatchable {
    use WithInitialization;

    public function dispatch(): PendingDispatch {
        return $this->ifInitialized(function () {
            return new PendingDispatch($this);
        });
    }

    public function run(): mixed {
        return $this->ifInitialized(function () {
            return app(Dispatcher::class)->dispatchNow($this);
        });
    }
}
