<?php declare(strict_types = 1);

use Illuminate\Container\Container;
use Package\Jobs\DoSomethingPackageJob;

// Use
Container::getInstance()->make(DoSomethingPackageJob::class)->dispatch();

// Instead of
// @phpstan-ignore-next-line
DoSomethingPackageJob::dispatch();
