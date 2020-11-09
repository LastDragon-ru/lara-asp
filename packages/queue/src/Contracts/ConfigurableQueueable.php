<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Contracts;

use Illuminate\Contracts\Queue\ShouldQueue;

interface ConfigurableQueueable extends ShouldQueue {
    public function getQueueConfig(): array;
}
