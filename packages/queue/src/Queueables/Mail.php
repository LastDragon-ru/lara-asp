<?php

namespace LastDragon_ru\LaraASP\Queue\Queueables;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use LastDragon_ru\LaraASP\Queue\Concerns\Configurable;
use LastDragon_ru\LaraASP\Queue\Contracts\ConfigurableQueueable;

abstract class Mail extends Mailable implements ShouldQueue, ConfigurableQueueable {
    use Queueable, SerializesModels, Configurable;
}
