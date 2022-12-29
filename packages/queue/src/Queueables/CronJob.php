<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Queueables;

use AllowDynamicProperties;
use LastDragon_ru\LaraASP\Queue\Contracts\Cronable;

#[AllowDynamicProperties]
abstract class CronJob extends Job implements Cronable {
    // empty
}
