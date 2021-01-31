<?php declare(strict_types = 1);

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use LastDragon_ru\LaraASP\Spa\Http\Controllers\SpaController;
use LastDragon_ru\LaraASP\Spa\Package;

$package    = Package::Name;
$prefix     = config("{$package}.routes.prefix");
$enabled    = config("{$package}.routes.enabled");
$middleware = config("{$package}.routes.middleware");

if (!$enabled) {
    return;
}

// SPA Routes
Route::group([
    'middleware' => $middleware,
    'prefix'     => $prefix,
], function (Router $router) {
    $router->get('settings', [SpaController::class, 'settings']);
    $router->get('user', [SpaController::class, 'user']);
});
