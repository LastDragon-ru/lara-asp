<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Testing\Package;

use LastDragon_ru\LaraASP\Spa\Provider;
use LastDragon_ru\LaraASP\Testing\Package\TestCase as PackageTestCase;

use function array_merge;

/**
 * @internal
 */
abstract class TestCase extends PackageTestCase {
    /**
     * @inheritDoc
     */
    protected function getPackageProviders(mixed $app): array {
        return array_merge(parent::getPackageProviders($app), [
            Provider::class,
        ]);
    }
}
