<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Queueables;

use LastDragon_ru\LaraASP\Queue\Contracts\Cronable;

abstract class CronJob extends Job implements Cronable {
    // empty
}
