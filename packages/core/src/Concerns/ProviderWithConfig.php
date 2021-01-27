<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Concerns;

use Illuminate\Contracts\Foundation\CachesConfiguration;
use LastDragon_ru\LaraASP\Core\Utils\ConfigRecursiveMerger;

/**
 * @mixin \Illuminate\Support\ServiceProvider
 */
trait ProviderWithConfig {
    protected function loadConfigFrom(string $path, string $key, bool $strict = true, array $unprotected = []): void {
        if (!($this->app instanceof CachesConfiguration && $this->app->configurationIsCached())) {
            $config = $this->app->make('config');

            $config->set($key, (new ConfigRecursiveMerger($strict, $unprotected))->merge(
                require $path, $config->get($key, [])
            ));
        }
    }
}
