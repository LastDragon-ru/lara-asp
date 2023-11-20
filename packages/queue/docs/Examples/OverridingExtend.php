<?php declare(strict_types = 1);

namespace App\Jobs;

use Override;
use Package\Jobs\DoSomethingPackageJob;

class DoSomethingAppJob extends DoSomethingPackageJob {
    #[Override]
    public function __invoke(): void {
        // our implementation
    }
}
