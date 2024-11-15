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
        'enabled'    => true,
        'middleware' => 'web',
        'prefix'     => 'spa_',
    ],

    /**
     * SPA Settings
     * ---------------------------------------------------------------------
     * You can define settings that should be available for SPA.
     */
    'spa'    => [
        // This value has no effect inside the published config.
        ConfigMerger::Strict => false,
        'property'           => 'value',
    ],
];
