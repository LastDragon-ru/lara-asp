<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Concerns;

/**
 * @mixin \Illuminate\Support\ServiceProvider
 */
trait ProviderWithRoutes {
    use ProviderHelper;

    protected function bootRoutes() {
        $this->callAfterBoot(function () {
            $this->loadRoutesFrom($this->getPath('../routes/routes.php'));
        });
    }
}
