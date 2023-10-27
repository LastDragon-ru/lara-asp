<?php declare(strict_types = 1);

namespace Package\Jobs;

use LastDragon_ru\LaraASP\Queue\Queueables\Job;

class DoSomethingPackageJob extends Job {
    public function __invoke(): void {
        // ...
    }
}
