<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Testing\Package;

use LastDragon_ru\LaraASP\Documentator\Provider;
use LastDragon_ru\LaraASP\Serializer\Provider as SerializerProvider;
use LastDragon_ru\LaraASP\Testing\Package\TestCase as PackageTestCase;
use Override;

/**
 * @internal
 */
abstract class TestCase extends PackageTestCase {
    /**
     * @inheritDoc
     */
    #[Override]
    protected function getPackageProviders(mixed $app): array {
        return [
            Provider::class,
            SerializerProvider::class,
        ];
    }
}
