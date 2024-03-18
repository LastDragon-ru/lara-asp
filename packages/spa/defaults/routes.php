<?php declare(strict_types = 1);

use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Routing\Registrar;
use LastDragon_ru\LaraASP\Spa\Http\Controllers\SpaController;
use LastDragon_ru\LaraASP\Spa\Package;

$repository = Container::getInstance()->make(Repository::class);
$package    = Package::Name;
$prefix     = $repository->get("{$package}.routes.prefix");
$enabled    = $repository->get("{$package}.routes.enabled");
$middleware = $repository->get("{$package}.routes.middleware");

if (!$enabled || !Container::getInstance()->bound(Registrar::class)) {
    return;
}

// SPA Routes
Container::getInstance()->make(Registrar::class)->group(
    [
        'middleware' => $middleware,
        'prefix'     => $prefix,
    ],
    static function (Registrar $router): void {
        $router->get('settings', [SpaController::class, 'settings']);
        $router->get('user', [SpaController::class, 'user']);
    },
);
