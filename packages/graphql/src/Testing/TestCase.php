<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing;

use GraphQL\Utils\SchemaPrinter;
use LastDragon_ru\LaraASP\GraphQL\Provider;
use LastDragon_ru\LaraASP\Testing\Package\TestCase as PackageTestCase;
use Nuwave\Lighthouse\GraphQL;
use Nuwave\Lighthouse\LighthouseServiceProvider;
use Nuwave\Lighthouse\Schema\Source\SchemaSourceProvider;
use Nuwave\Lighthouse\Testing\TestSchemaProvider;

use function str_replace;

use const PHP_EOL;

class TestCase extends PackageTestCase {
    /**
     * @inheritdoc
     */
    protected function getPackageProviders($app): array {
        return [
            Provider::class,
            LighthouseServiceProvider::class,
        ];
    }

    protected function getSchema(string $schema): string {
        $this->app->bind(SchemaSourceProvider::class, static function () use ($schema): SchemaSourceProvider {
            return new TestSchemaProvider($schema);
        });

        $graphql = $this->app->make(GraphQL::class);
        $schema  = $graphql->prepSchema();
        $schema  = SchemaPrinter::doPrint($schema);
        $schema  = str_replace(PHP_EOL, "\n", $schema);

        return $schema;
    }
}
