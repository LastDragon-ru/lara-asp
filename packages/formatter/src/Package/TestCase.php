<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Package;

use LastDragon_ru\LaraASP\Core\PackageProvider as CoreProvider;
use LastDragon_ru\LaraASP\Formatter\PackageProvider;
use LastDragon_ru\LaraASP\Testing\Testing\TestCase as PackageTestCase;
use Override;

use function array_merge;

/**
 * @internal
 */
abstract class TestCase extends PackageTestCase {
    /**
     * @inheritDoc
     */
    #[Override]
    protected function getPackageProviders(mixed $app): array {
        return array_merge(parent::getPackageProviders($app), [
            CoreProvider::class,
            PackageProvider::class,
        ]);
    }
}
