<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Testing\Package;

use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Queue\Provider;
use LastDragon_ru\LaraASP\Testing\Package\TestCase as PackageTestCase;

/**
 * @internal
 */
abstract class TestCase extends PackageTestCase {
    /**
     * @return array<class-string<ServiceProvider>>
     */
    protected function getPackageProviders(mixed $app): array {
        return [
            Provider::class,
        ];
    }
}
