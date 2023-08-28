<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Provider;

use Illuminate\Contracts\Foundation\CachesConfiguration;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Utils\ConfigMerger;

use function config;

/**
 * @mixin ServiceProvider
 */
trait WithConfig {
    use Helper;

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
            $merger  = new ConfigMerger();
            $target  = (array) require $path;
            $current = (array) config($key, []);

            config([
                $key => $merger->merge($target, $current),
            ]);
        }
    }
}
