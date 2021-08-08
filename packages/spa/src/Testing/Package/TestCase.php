<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Testing\Package;

use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Spa\Provider;
use LastDragon_ru\LaraASP\Testing\Package\TestCase as PackageTestCase;

use function array_merge;

/**
 * @internal
 */
abstract class TestCase extends PackageTestCase {
    /**
     * @return array<class-string<ServiceProvider>>
     */
    protected function getPackageProviders(mixed $app): array {
        return array_merge(parent::getPackageProviders($app), [
            Provider::class,
        ]);
    }
}
