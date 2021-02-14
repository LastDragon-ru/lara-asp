<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Concerns;

use Illuminate\Contracts\Foundation\CachesConfiguration;
use LastDragon_ru\LaraASP\Core\Utils\ConfigRecursiveMerger;

/**
 * @mixin \Illuminate\Support\ServiceProvider
 */
trait ProviderWithConfig {
    use ProviderHelper;

    protected function bootConfig(array $unprotected = []): void {
        $package = $this->getName();
        $path    = $this->getPath('../config/config.php');

        $this->loadConfigFrom($path, $package, true, $unprotected);
        $this->publishes([
            $path => $this->app->configPath("{$package}.php"),
        ], 'config');
    }

    protected function loadConfigFrom(string $path, string $key, bool $strict = true, array $unprotected = []): void {
        if (!($this->app instanceof CachesConfiguration && $this->app->configurationIsCached())) {
            $config = $this->app->make('config');

            $config->set($key, (new ConfigRecursiveMerger($strict, $unprotected))->merge(
                require $path,
                $config->get($key, []),
            ));
        }
    }
}
