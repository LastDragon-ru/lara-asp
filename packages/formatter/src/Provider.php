<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter;

use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Concerns\ProviderWithConfig;
use function tap;

class Provider extends ServiceProvider {
    use ProviderWithConfig;

    public const Package = 'lara-asp-formatter';

    public function boot(): void {
        tap(__DIR__.'/../config/config.php', function (string $path): void {
            $this->loadConfigFrom($path, $this->getPackage(), false);
            $this->publishes([
                $path => $this->app->configPath("{$this->getPackage()}.php"),
            ], 'config');
        });

        tap(__DIR__.'/../resources/lang', function (string $path): void {
            $this->loadTranslationsFrom($path, $this->getPackage());
            $this->publishes([
                $path => $this->app->resourcePath("lang/vendor/{$this->getPackage()}"),
            ], 'translations');
        });
    }

    protected function getPackage(): string {
        return static::Package;
    }
}
