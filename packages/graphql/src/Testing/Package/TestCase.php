<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package;

use Illuminate\Contracts\Container\Container;
use LastDragon_ru\LaraASP\GraphQL\Provider;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\SchemaPrinter;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQL\Testing\GraphQLAssertions;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\SchemaPrinter\LighthouseDirectiveFilter;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\SchemaPrinter\TestSettings;
use LastDragon_ru\LaraASP\Testing\Package\TestCase as PackageTestCase;
use Nuwave\Lighthouse\LighthouseServiceProvider;

class TestCase extends PackageTestCase {
    use GraphQLAssertions;

    /**
     * @inheritDoc
     */
    protected function getPackageProviders(mixed $app): array {
        return [
            Provider::class,
            LighthouseServiceProvider::class,
        ];
    }

    public function getContainer(): Container {
        return parent::getContainer();
    }

    protected function getGraphQLSchemaPrinter(Settings $settings = null): SchemaPrinter {
        return $this->app->make(SchemaPrinter::class)->setSettings(
            $settings ?? (new TestSettings())->setDirectiveDefinitionFilter(new LighthouseDirectiveFilter()),
        );
    }
}
