<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa;

use Closure;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Concerns\ProviderWithConfig;
use function tap;

class Provider extends ServiceProvider {
    use ProviderWithConfig;

    public const Package = 'lara-asp-spa';

    protected function getPackage(): string {
        return static::Package;
    }

    public function boot(): void {
        $this->bootConfig();
        $this->bootRoutes();
    }

    protected function bootConfig(): void {
        tap(__DIR__.'/../config/config.php', function (string $path): void {
            $this->loadConfigFrom($path, $this->getPackage(), true, [
                'spa',
            ]);
            $this->publishes([
                $path => $this->app->configPath("{$this->getPackage()}.php"),
            ], 'config');
        });
    }

    protected function bootRoutes() {
        $this->callAfterBoot(function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/routes.php');
        });
    }

    protected function callAfterBoot(Closure $callback) {
        if ($this->app instanceof Application && $this->app->isBooted()) {
            $this->app->call($callback);
        } else {
            $this->booted($callback);
        }
    }
}
