<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Concerns\ProviderWithConfig;
use LastDragon_ru\LaraASP\Core\Concerns\ProviderWithRoutes;
use LastDragon_ru\LaraASP\Core\Concerns\ProviderWithTranslations;
use LastDragon_ru\LaraASP\Spa\Routing\UnresolvedValueException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Provider extends ServiceProvider {
    use ProviderWithConfig;
    use ProviderWithRoutes;
    use ProviderWithTranslations;

    // <editor-fold desc="\Illuminate\Support\ServiceProvider">
    // =========================================================================
    public function boot(): void {
        $this->bootConfig([
            'spa',
        ]);
        $this->bootRoutes();
        $this->bootTranslations();
        $this->bootExceptionHandler();
    }
    // </editor-fold>

    // <editor-fold desc="Functions">
    // =========================================================================
    protected function getName(): string {
        return Package::Name;
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
    // </editor-fold>
}
