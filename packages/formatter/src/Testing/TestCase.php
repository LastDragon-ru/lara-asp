<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Testing;

use LastDragon_ru\LaraASP\Formatter\Provider;
use LastDragon_ru\LaraASP\Testing\Package\TestCase as PackageTestCase;

class TestCase extends PackageTestCase {
    /**
     * @inheritdoc
     */
    protected function getPackageProviders($app): array {
        return [
            Provider::class,
        ];
    }
}
