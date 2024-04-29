<?php declare(strict_types = 1);

use Illuminate\Http\Request;

// phpcs:disable PSR1.Files.SideEffects

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
$maintenance = __DIR__.'/../storage/framework/maintenance.php';

if (file_exists($maintenance)) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
