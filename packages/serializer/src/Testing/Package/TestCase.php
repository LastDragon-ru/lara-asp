<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer\Testing\Package;

use LastDragon_ru\LaraASP\Serializer\Provider;
use LastDragon_ru\LaraASP\Testing\Package\TestCase as PackageTestCase;
use Override;

/**
 * @internal
 */
class TestCase extends PackageTestCase {
    /**
     * @inheritDoc
     */
    #[Override]
    protected function getPackageProviders(mixed $app): array {
        return [
            Provider::class,
        ];
    }
}
