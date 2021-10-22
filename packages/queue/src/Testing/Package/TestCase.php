<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Testing\Package;

use LastDragon_ru\LaraASP\Queue\Provider;
use LastDragon_ru\LaraASP\Testing\Package\TestCase as PackageTestCase;

/**
 * @internal
 */
abstract class TestCase extends PackageTestCase {
    /**
     * @inheritDoc
     */
    protected function getPackageProviders(mixed $app): array {
        return [
            Provider::class,
        ];
    }
}
