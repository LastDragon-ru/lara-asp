<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core;

use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Routing\AcceptValidator;
use function array_merge;

class Provider extends ServiceProvider {
    // <editor-fold desc="\Illuminate\Support\ServiceProvider">
    // =========================================================================
    public function register() {
        parent::register();

        $this->registerRoutingValidator();
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
    // </editor-fold>
}
