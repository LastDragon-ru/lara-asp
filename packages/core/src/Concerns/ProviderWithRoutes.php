<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Concerns;

use Illuminate\Support\ServiceProvider;

/**
 * @mixin ServiceProvider
 */
trait ProviderWithRoutes {
    use ProviderHelper;

    protected function bootRoutes(): void {
        $this->callAfterBoot(function (): void {
            $this->loadRoutesFrom($this->getPath('../routes/routes.php'));
        });
    }
}
