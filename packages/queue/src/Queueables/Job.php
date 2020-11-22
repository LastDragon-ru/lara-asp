<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Queueables;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use LastDragon_ru\LaraASP\Queue\Concerns\Configurable;
use LastDragon_ru\LaraASP\Queue\Concerns\Dispatchable;
use LastDragon_ru\LaraASP\Queue\Contracts\ConfigurableQueueable;

abstract class Job implements ShouldQueue, ConfigurableQueueable {
    use Queueable, SerializesModels, Configurable, Dispatchable;
}
