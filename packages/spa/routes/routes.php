<?php declare(strict_types = 1);

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use LastDragon_ru\LaraASP\Spa\Provider;

$package    = Provider::Package;
$prefix     = config("{$package}.routes.prefix");
$enabled    = config("{$package}.routes.enabled");
$middleware = config("{$package}.routes.middleware");
$controller = config("{$package}.routes.controller");

if (!$enabled) {
    return;
}

// SPA Routes
Route::group([
    'middleware' => $middleware,
    'prefix'     => $prefix,
], function (Router $router) use ($controller) {
    $router->get('settings', [$controller, 'settings']);
});
