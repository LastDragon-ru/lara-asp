<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Provider;

use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\CachesConfiguration;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Utils\ConfigMerger;

/**
 * @mixin ServiceProvider
 */
trait WithConfig {
    use Helper;

    protected function bootConfig(): void {
        $package = $this->getName();
        $path    = $this->getPath('../defaults/config.php');

        $this->loadConfigFrom($path, $package);
        $this->publishes([
            $path => $this->app->configPath("{$package}.php"),
        ], 'config');
    }

    protected function loadConfigFrom(string $path, string $key): void {
        if (!($this->app instanceof CachesConfiguration && $this->app->configurationIsCached())) {
            $repository = Container::getInstance()->make(Repository::class);
            $merger     = new ConfigMerger();
            $target     = (array) require $path;
            $current    = (array) $repository->get($key, []);

            $repository->set([
                $key => $merger->merge($target, $current),
            ]);
        }
    }
}
