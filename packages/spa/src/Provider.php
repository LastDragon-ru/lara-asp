<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa;

use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Provider\WithConfig;
use LastDragon_ru\LaraASP\Core\Provider\WithRoutes;
use LastDragon_ru\LaraASP\Core\Provider\WithTranslations;
use LastDragon_ru\LaraASP\Spa\Routing\UnresolvedValueException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Provider extends ServiceProvider {
    use WithConfig;
    use WithRoutes;
    use WithTranslations;

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
    protected function getName(): string {
        return Package::Name;
    }

    protected function bootExceptionHandler(): void {
        $this->callAfterResolving(ExceptionHandler::class, static function (ExceptionHandler $handler): void {
            if (!($handler instanceof Handler)) {
                return;
            }

            $handler->map(
                UnresolvedValueException::class,
                static function (UnresolvedValueException $exception): Exception {
                    return new NotFoundHttpException($exception->getMessage() ?: 'Not found.', $exception);
                },
            );
        });
    }
    // </editor-fold>
}
