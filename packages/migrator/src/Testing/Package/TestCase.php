<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Testing\Package;

use LastDragon_ru\LaraASP\Migrator\Provider;
use LastDragon_ru\LaraASP\Testing\Package\TestCase as PackageTestCase;

class TestCase extends PackageTestCase {
    /**
     * @inheritDoc
     */
    protected function getPackageProviders(mixed $app): array {
        return [
            Provider::class,
        ];
    }
}
