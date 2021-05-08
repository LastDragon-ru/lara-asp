<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing;

use GraphQL\Type\Schema;
use GraphQL\Utils\SchemaPrinter;
use Illuminate\Contracts\Config\Repository;
use LastDragon_ru\LaraASP\Testing\Utils\Args;
use Nuwave\Lighthouse\Schema\SchemaBuilder;
use Nuwave\Lighthouse\Schema\Source\SchemaSourceProvider;
use Nuwave\Lighthouse\Schema\Source\SchemaStitcher;
use Nuwave\Lighthouse\Testing\MocksResolvers;
use Nuwave\Lighthouse\Testing\TestSchemaProvider;
use SplFileInfo;

/**
 * @mixin \PHPUnit\Framework\TestCase
 */
trait GraphQLAssertions {
    use MocksResolvers;

    // <editor-fold desc="Assertions">
    // =========================================================================
    /**
     * Compares two GraphQL schemas.
     */
    public function assertGraphQLSchemaEquals(
        SplFileInfo|string $expected,
        Schema|SplFileInfo|string $schema,
        string $message = '',
    ): void {
        $this->assertEquals(
            Args::content($expected),
            $this->printGraphQLSchema($schema),
            $message,
        );
    }

    /**
     * Compares GraphQL schema with default (application) schema.
     */
    public function assertDefaultGraphQLSchemaEquals(
        SplFileInfo|string $expected,
        string $message = '',
    ): void {
        $this->assertGraphQLSchemaEquals(
            $expected,
            $this->getDefaultGraphQLSchema(),
            $message,
        );
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    protected function getGraphQLSchema(SplFileInfo|string $schema): Schema {
        $schema = Args::content($schema);

        $this->app->bind(SchemaSourceProvider::class, static function () use ($schema): SchemaSourceProvider {
            return new TestSchemaProvider($schema);
        });

        $graphql = $this->app->make(SchemaBuilder::class);
        $schema  = $graphql->schema();

        return $schema;
    }

    protected function getDefaultGraphQLSchema(): Schema {
        $this->app->bind(SchemaSourceProvider::class, function (): SchemaSourceProvider {
            return new SchemaStitcher(
                $this->app->make(Repository::class)->get('lighthouse.schema.register', ''),
            );
        });

        $graphql = $this->app->make(SchemaBuilder::class);
        $schema  = $graphql->schema();

        return $schema;
    }

    protected function printGraphQLSchema(Schema|SplFileInfo|string $schema): string {
        if (!($schema instanceof Schema)) {
            $schema = $this->getGraphQLSchema($schema);
        }

        return SchemaPrinter::doPrint($schema);
    }

    protected function printDefaultGraphQLSchema(): string {
        return $this->printGraphQLSchema($this->getDefaultGraphQLSchema());
    }
    // </editor-fold>
}
