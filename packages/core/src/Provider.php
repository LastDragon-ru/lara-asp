<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Routing\AcceptValidator;
use LastDragon_ru\LaraASP\Core\Routing\UnresolvedValueException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use function array_merge;
use function tap;

class Provider extends ServiceProvider {
    public const Package = 'lara-asp-core';

    protected function getPackage(): string {
        return static::Package;
    }

    // <editor-fold desc="\Illuminate\Support\ServiceProvider">
    // =========================================================================
    public function register() {
        parent::register();

        $this->registerRoutingValidator();
    }

    public function boot(): void {
        $this->bootExceptionHandler();
        $this->bootTranslations();
    }
    // </editor-fold>

    // <editor-fold desc="Functions">
    // =========================================================================
    protected function registerRoutingValidator() {
        // https://viblo.asia/p/implementing-custom-route-validators-for-laravel-DbmemLxyGAg
        Route::$validators = array_merge(Route::getValidators(), [
            $this->app->make(AcceptValidator::class),
        ]);
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

    protected function bootTranslations() {
        tap(__DIR__.'/../resources/lang', function (string $path): void {
            $this->loadTranslationsFrom($path, $this->getPackage());
            $this->publishes([
                $path => $this->app->resourcePath("lang/vendor/{$this->getPackage()}"),
            ], 'translations');
        });
    }
    // </editor-fold>
}
