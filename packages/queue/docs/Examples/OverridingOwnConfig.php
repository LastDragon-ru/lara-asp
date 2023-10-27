<?php declare(strict_types = 1);

namespace App\Jobs;

use LastDragon_ru\LaraASP\Queue\Concerns\WithConfig;
use Package\Jobs\DoSomethingPackageJob;

class DoSomethingAppJob extends DoSomethingPackageJob {
    use WithConfig; // Indicates that the job has its own config

    public function __invoke(): void {
        // our implementation
    }
}
