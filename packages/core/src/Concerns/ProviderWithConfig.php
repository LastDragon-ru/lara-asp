<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Concerns;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\CachesConfiguration;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Utils\ConfigMerger;

/**
 * @mixin ServiceProvider
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
            $config = $this->app->make(Repository::class);
            $target = require $path;

            $config->set($key, (new ConfigMerger())->merge(
                (array) $target,
                (array) $config->get($key, []),
            ));
        }
    }
}
