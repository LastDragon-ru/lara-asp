<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Provider;

use Closure;
use Illuminate\Contracts\Foundation\CachesRoutes;
use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Support\ServiceProvider;

use function assert;

/**
 * @phpstan-require-extends ServiceProvider
 */
trait WithRoutes {
    use Helper;

    /**
     * @param Closure(): array{enabled: bool, attributes: array<string, mixed>} $settings
     */
    protected function bootRoutes(Closure $settings): void {
        // Cached?
        if ($this->app instanceof CachesRoutes && $this->app->routesAreCached()) {
            return;
        }

        // Load (config may be incomplete until boot)
        $this->booted(function () use ($settings): void {
            // Enabled?
            $settings = $settings();

            if (!$settings['enabled'] || !$this->app->bound(Registrar::class)) {
                return;
            }

            // Add
            $path      = $this->getPath('../defaults/routes.php');
            $routes    = require $path;
            $registrar = $this->app->make(Registrar::class);

            assert($routes instanceof Closure);

            $registrar->group($settings['attributes'], $routes);
        });
    }
}
