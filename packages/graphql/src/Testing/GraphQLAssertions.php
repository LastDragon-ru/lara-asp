<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing;

use GraphQL\Type\Schema;
use Illuminate\Contracts\Config\Repository;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\Printer;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\SchemaPrinter\TestSettings;
use LastDragon_ru\LaraASP\Testing\Utils\Args;
use Nuwave\Lighthouse\Schema\SchemaBuilder;
use Nuwave\Lighthouse\Schema\Source\SchemaSourceProvider;
use Nuwave\Lighthouse\Schema\Source\SchemaStitcher;
use Nuwave\Lighthouse\Testing\MocksResolvers;
use Nuwave\Lighthouse\Testing\TestSchemaProvider;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

/**
 * @mixin TestCase
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
        self::assertEquals(
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
        self::assertGraphQLSchemaEquals(
            $expected,
            $this->getDefaultGraphQLSchema(),
            $message,
        );
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    protected function useGraphQLSchema(SplFileInfo|string $schema): static {
        $schema = Args::content($schema);

        $this->override(SchemaSourceProvider::class, static function () use ($schema): SchemaSourceProvider {
            return new TestSchemaProvider($schema);
        });

        return $this;
    }

    protected function getGraphQLSchema(SplFileInfo|string $schema): Schema {
        $this->useGraphQLSchema($schema);

        $graphql = $this->app->make(SchemaBuilder::class);
        $schema  = $graphql->schema();

        return $schema;
    }

    protected function getDefaultGraphQLSchema(): Schema {
        $this->override(SchemaSourceProvider::class, function (): SchemaSourceProvider {
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

        return (string) $this->getGraphQLSchemaPrinter()->print($schema);
    }

    protected function printDefaultGraphQLSchema(): string {
        return $this->printGraphQLSchema($this->getDefaultGraphQLSchema());
    }

    protected function getGraphQLSchemaPrinter(): Printer {
        return $this->app->make(Printer::class)->setSettings(new TestSettings());
    }
    // </editor-fold>
}
