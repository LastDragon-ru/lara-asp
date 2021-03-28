<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package;

use GraphQL\Utils\SchemaPrinter;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\Provider;
use LastDragon_ru\LaraASP\Testing\Package\TestCase as PackageTestCase;
use Nuwave\Lighthouse\GraphQL;
use Nuwave\Lighthouse\LighthouseServiceProvider;
use Nuwave\Lighthouse\Schema\Source\SchemaSourceProvider;
use Nuwave\Lighthouse\Testing\TestSchemaProvider;

use function array_column;
use function array_unique;
use function count;
use function preg_match_all;
use function rsort;
use function str_replace;

use const PREG_SET_ORDER;

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

        return $schema;
    }

    /**
     * @return array{sql: string, bindings: array<mixed>}
     */
    protected function getSql(EloquentBuilder|QueryBuilder $builder): array {
        $sql     = $builder->toSql();
        $matches = [];

        if (preg_match_all('/(?<group>laravel_reserved_[\d]+)/', $sql, $matches, PREG_SET_ORDER)) {
            $matches = array_unique(array_column($matches, 'group'));
            $index   = count($matches);

            rsort($matches);

            foreach ($matches as $match) {
                $index = $index - 1;
                $sql   = str_replace($match, "table_alias_{$index}", $sql);
            }
        }

        return [
            'sql'      => $sql,
            'bindings' => $builder->getBindings(),
        ];
    }
}
