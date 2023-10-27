<?php declare(strict_types = 1);

use App\Jobs\MyJobWithConfig;

// config/queue.php

return [
    // .....

    /*
    |--------------------------------------------------------------------------
    | Queueables Configuration
    |--------------------------------------------------------------------------
    |
    | These options configure the behavior of custom queue jobs.
    |
    */
    'queueables' => [
        MyJobWithConfig::class => [
            'settings' => [
                'expire' => '8 hours',
            ],
        ],
    ],
];
