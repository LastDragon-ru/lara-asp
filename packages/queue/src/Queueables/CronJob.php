<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Queueables;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use LastDragon_ru\LaraASP\Queue\Concerns\Configurable;
use LastDragon_ru\LaraASP\Queue\Contracts\Cronable;

abstract class CronJob implements ShouldQueue, Cronable {
    use Queueable, SerializesModels, Configurable;
}
