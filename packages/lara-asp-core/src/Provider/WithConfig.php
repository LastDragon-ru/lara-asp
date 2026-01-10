<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Provider;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\CachesConfiguration;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Application\Configuration\Configuration;
use LastDragon_ru\LaraASP\Core\Application\Configuration\ConfigurationResolver;
use LastDragon_ru\LaraASP\Core\Package;
use LastDragon_ru\LaraASP\Core\Utils\ConfigMerger;

use function is_array;
use function trigger_deprecation;

/**
 * @see Configuration
 *
 * @phpstan-require-extends ServiceProvider
 */
trait WithConfig {
    use Helper;

    /**
     * @deprecated 7.0.0 Please migrate to {@see self::registerConfig()} and object-based config.
     */
    protected function bootConfig(): void {
        trigger_deprecation(
            Package::Name,
            '7.0.0',
            'Please migrate to `self::registerConfig()` and object-based config.',
        );

        $package = $this->getName();
        $path    = $this->getPath('../defaults/config.php');

        $this->loadConfigFrom($path, $package);
        $this->publishes([
            $path => $this->app->configPath("{$package}.php"),
        ], 'config');
    }

    protected function loadConfigFrom(string $path, string $key): void {
        if (!($this->app instanceof CachesConfiguration && $this->app->configurationIsCached())) {
            $repository = $this->app->make(Repository::class);
            $merger     = new ConfigMerger();
            $target     = (array) require $path;
            $current    = (array) $repository->get($key, []);

            $repository->set([
                $key => $merger->merge($target, $current),
            ]);
        }
    }

    /**
     * @template C of Configuration
     * @template T of ConfigurationResolver<C>
     *
     * @param class-string<T> $resolver
     */
    protected function registerConfig(string $resolver): void {
        $package = $this->getName();

        $this->app->singletonIf($resolver);
        $this->loadPackageConfig($resolver);
        $this->publishes([
            $this->getPath('../defaults/config.php') => $this->app->configPath("{$package}.php"),
        ], 'config');
    }

    /**
     * @param class-string<ConfigurationResolver<covariant Configuration>> $resolver
     */
    private function loadPackageConfig(string $resolver): void {
        if (!($this->app instanceof CachesConfiguration && $this->app->configurationIsCached())) {
            $repository = $this->app->make(Repository::class);
            $package    = $this->getName();
            $current    = $repository->get($package, null);

            if ($current === null) {
                $repository->set([
                    $package => $resolver::getDefaultConfig(),
                ]);
            } elseif (is_array($current)) {
                // todo(lara-asp-core): Remove somewhere in v9 or later.
                trigger_deprecation(
                    Package::Name,
                    '7.0.0',
                    'Array-based config is deprecated. Please migrate to object-based config.',
                );

                $repository->set([
                    $package => $resolver::getDefaultConfig()::fromArray((new ConfigMerger())->merge($current, [])),
                ]);
            } else {
                // empty
            }
        }
    }
}
