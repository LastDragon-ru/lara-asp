<?php

namespace LastDragon_ru\LaraASP\Queue\Queueables;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use LastDragon_ru\LaraASP\Queue\Concerns\Configurable;
use LastDragon_ru\LaraASP\Queue\Contracts\ConfigurableQueueable;

abstract class Listener implements ShouldQueue, ConfigurableQueueable {
    use Queueable, SerializesModels, Configurable;
}
