<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa;

use Closure;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Concerns\ProviderWithConfig;
use LastDragon_ru\LaraASP\Spa\Routing\UnresolvedValueException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use function tap;

class Provider extends ServiceProvider {
    use ProviderWithConfig;

    public const Package = 'lara-asp-spa';

    protected function getPackage(): string {
        return static::Package;
    }

    // <editor-fold desc="\Illuminate\Support\ServiceProvider">
    // =========================================================================
    public function boot(): void {
        $this->bootConfig();
        $this->bootRoutes();
        $this->bootTranslations();
        $this->bootExceptionHandler();
    }
    // </editor-fold>

    // <editor-fold desc="Functions">
    // =========================================================================
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

    protected function bootTranslations() {
        tap(__DIR__.'/../resources/lang', function (string $path): void {
            $this->loadTranslationsFrom($path, $this->getPackage());
            $this->publishes([
                $path => $this->app->resourcePath("lang/vendor/{$this->getPackage()}"),
            ], 'translations');
        });
    }

    protected function bootExceptionHandler() {
        $this->callAfterResolving(ExceptionHandler::class, function (ExceptionHandler $handler) {
            if (!($handler instanceof Handler)) {
                return;
            }

            $handler->map(UnresolvedValueException::class, function (UnresolvedValueException $exception) {
                return new NotFoundHttpException($exception->getMessage() ?: 'Not found.', $exception);
            });
        });
    }

    protected function callAfterBoot(Closure $callback) {
        if ($this->app instanceof Application && $this->app->isBooted()) {
            $this->app->call($callback);
        } else {
            $this->booted($callback);
        }
    }
    // </editor-fold>
}
