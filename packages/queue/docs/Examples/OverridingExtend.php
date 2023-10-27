<?php declare(strict_types = 1);

namespace App\Jobs;

use Package\Jobs\DoSomethingPackageJob;

class DoSomethingAppJob extends DoSomethingPackageJob {
    public function __invoke(): void {
        // our implementation
    }
}
