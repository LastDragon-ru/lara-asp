<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package;

use GraphQL\Type\Schema;
use Illuminate\Contracts\Container\Container;
use LastDragon_ru\LaraASP\GraphQL\Provider;
use LastDragon_ru\LaraASP\GraphQL\Testing\GraphQLAssertions;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Provider as TestProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\SchemaPrinter\LighthouseDirectiveFilter;
use LastDragon_ru\LaraASP\GraphQL\Utils\ArgumentFactory;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Printer as PrinterContract;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Printer;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\TestSettings;
use LastDragon_ru\LaraASP\Testing\Package\TestCase as PackageTestCase;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Nuwave\Lighthouse\LighthouseServiceProvider;
use Nuwave\Lighthouse\Testing\TestingServiceProvider as LighthousTestingServiceProvider;
use SplFileInfo;

/**
 * @internal
 */
class TestCase extends PackageTestCase {
    use GraphQLAssertions {
        getGraphQLPrinter as private getDefaultGraphQLPrinter;
    }

    /**
     * @inheritDoc
     */
    protected function getPackageProviders(mixed $app): array {
        return [
            Provider::class,
            TestProvider::class,
            LighthouseServiceProvider::class,
            LighthousTestingServiceProvider::class,
        ];
    }

    public function getContainer(): Container {
        return parent::getContainer();
    }

    /**
     * @return PrinterContract&Printer
     */
    protected function getGraphQLPrinter(Settings $settings = null): PrinterContract {
        $settings ??= (new TestSettings())
            ->setDirectiveDefinitionFilter($this->app->make(LighthouseDirectiveFilter::class));
        $printer    = $this->getDefaultGraphQLPrinter($settings);

        return $printer;
    }

    protected function getGraphQLArgument(
        string $type,
        mixed $value,
        Schema|SplFileInfo|string $schema = null,
    ): Argument {
        try {
            if ($schema) {
                $this->useGraphQLSchema($schema);
            }

            $factory  = $this->app->make(ArgumentFactory::class);
            $argument = $factory->getArgument($type, $value);

            return $argument;
        } finally {
            $this->resetGraphQLSchema();
        }
    }
}
