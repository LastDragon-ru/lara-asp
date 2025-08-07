<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package;

use LastDragon_ru\LaraASP\Core\PackageProvider as CorePackageProvider;
use LastDragon_ru\LaraASP\GraphQL\PackageProvider as GraphQLPackageProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\GraphQLAssertions;
use LastDragon_ru\LaraASP\Testing\Testing\TestCase as PackageTestCase;
use Nuwave\Lighthouse\LighthouseServiceProvider;
use Nuwave\Lighthouse\Testing\TestingServiceProvider as LighthouseTestingServiceProvider;
use Nuwave\Lighthouse\Validation\ValidationServiceProvider as LighthouseValidationServiceProvider;
use Override;

use function array_merge;

/**
 * @internal
 */
abstract class TestCase extends PackageTestCase {
    use GraphQLAssertions;

    /**
     * @inheritDoc
     */
    #[Override]
    protected function getPackageProviders(mixed $app): array {
        return array_merge(parent::getPackageProviders($app), [
            Provider::class,
            CorePackageProvider::class,
            GraphQLPackageProvider::class,
            LighthouseServiceProvider::class,
            LighthouseTestingServiceProvider::class,
            LighthouseValidationServiceProvider::class,
        ]);
    }
}
