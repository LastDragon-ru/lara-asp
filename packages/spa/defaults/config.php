<?php declare(strict_types = 1);

use LastDragon_ru\LaraASP\Core\Utils\ConfigMerger;

/**
 * -----------------------------------------------------------------------------
 * SPA Settings
 * -----------------------------------------------------------------------------
 */
return [
    /**
     * Routes Settings
     * ---------------------------------------------------------------------
     */
    'routes' => [
        'enabled'    => false,
        'middleware' => 'web',
        'prefix'     => null,
    ],

    /**
     * SPA Settings
     * ---------------------------------------------------------------------
     * You can define settings that should be available for SPA.
     */
    'spa'    => [
        // This value has no effect inside the published config.
        ConfigMerger::Strict => false,
    ],
];
