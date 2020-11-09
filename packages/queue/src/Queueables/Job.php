<?php

namespace LastDragon_ru\LaraASP\Queue\Queueables;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use LastDragon_ru\LaraASP\Queue\Concerns\Configurable;
use LastDragon_ru\LaraASP\Queue\Concerns\Dispatchable;

abstract class Job implements ShouldQueue {
    use Queueable, SerializesModels, Configurable, Dispatchable;
}
