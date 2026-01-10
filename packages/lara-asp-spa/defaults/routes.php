<?php declare(strict_types = 1);

use Illuminate\Contracts\Routing\Registrar;
use LastDragon_ru\LaraASP\Spa\Http\Controllers\SpaController;

return static function (Registrar $router): void {
    $router->get('settings', [SpaController::class, 'settings']);
    $router->get('user', [SpaController::class, 'user']);
};
