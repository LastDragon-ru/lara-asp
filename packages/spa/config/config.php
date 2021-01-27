<?php declare(strict_types = 1);

/**
 * -----------------------------------------------------------------------------
 * SPA Settings
 * -----------------------------------------------------------------------------
 */

use LastDragon_ru\LaraASP\Spa\Http\Controllers\SpaController;

return [
    /**
     * Routes?
     * ---------------------------------------------------------------------
     * If `true` package will add default routes.
     */
    'routes' => [
        'enabled'    => true,
        'middleware' => 'web',
        'controller' => SpaController::class,
        'prefix'     => null,
    ],

    /**
     * SPA Settings
     * ---------------------------------------------------------------------
     * You can define settings that should be available for SPA.
     */
    'spa'    => [
        // empty
    ],
];
