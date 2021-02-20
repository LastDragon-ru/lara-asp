<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Contracts;

use Illuminate\Contracts\Queue\ShouldQueue;

interface ConfigurableQueueable extends ShouldQueue {
    /**
     * @return array<string, mixed>
     */
    public function getQueueConfig(): array;
}
