<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Concerns;

use Illuminate\Contracts\Foundation\CachesConfiguration;
use LastDragon_ru\LaraASP\Core\Utils\ConfigMerger;

/**
 * @mixin \Illuminate\Support\ServiceProvider
 */
trait ProviderWithConfig {
    use ProviderHelper;

    protected function bootConfig(): void {
        $package = $this->getName();
        $path    = $this->getPath('../config/config.php');

        $this->loadConfigFrom($path, $package);
        $this->publishes([
            $path => $this->app->configPath("{$package}.php"),
        ], 'config');
    }

    protected function loadConfigFrom(string $path, string $key): void {
        if (!($this->app instanceof CachesConfiguration && $this->app->configurationIsCached())) {
            $config = $this->app->make('config');

            $config->set($key, (new ConfigMerger())->merge(
                require $path,
                $config->get($key, []),
            ));
        }
    }
}
