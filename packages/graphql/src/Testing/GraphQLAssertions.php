<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\NodeList;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Printer;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\GraphQLAssertions as PrinterGraphQLAssertions;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\GraphQLExpected;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\TestSettings;
use LastDragon_ru\LaraASP\Testing\Utils\Args;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\SchemaBuilder;
use Nuwave\Lighthouse\Testing\MocksResolvers;
use Nuwave\Lighthouse\Testing\TestSchemaProvider;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

use function assert;

/**
 * @mixin TestCase
 */
trait GraphQLAssertions {
    use MocksResolvers;
    use PrinterGraphQLAssertions;

    // <editor-fold desc="Assertions">
    // =========================================================================
    /**
     * Compares GraphQL schema with current (application) schema.
     */
    public function assertGraphQLSchemaEquals(
        GraphQLExpected|SplFileInfo|string $expected,
        string $message = '',
    ): void {
        self::assertGraphQLPrintableEquals(
            $expected,
            $this->getGraphQLSchema(),
            $message,
        );
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    /**
     * Sets the current (application) schema to the given.
     */
    protected function useGraphQLSchema(Schema|DocumentNode|DocumentAST|SplFileInfo|string $schema): static {
        if ($schema instanceof DocumentAST) {
            // We just need all definitions
            $schema = new DocumentNode([
                'definitions' => (new NodeList([]))
                    ->merge($schema->types)
                    ->merge($schema->typeExtensions)
                    ->merge($schema->directives),
            ]);
        }

        $schema   = $schema instanceof Schema || $schema instanceof DocumentNode
            ? (string) $this->getGraphQLPrinter()->print($schema)
            : Args::content($schema);
        $provider = new TestSchemaProvider($schema);

        $this->getGraphQLSchemaBuilder()->setSchema($provider);

        return $this;
    }

    /**
     * Resets the current (application) schema to the default schema.
     */
    protected function resetGraphQLSchema(): static {
        $this->getGraphQLSchemaBuilder()->setSchema(null);

        return $this;
    }

    protected function getGraphQLSchema(): Schema {
        return $this->getGraphQLSchemaBuilder()->schema();
    }

    protected function getGraphQLPrinter(Settings $settings = null): Printer {
        return $this->app->make(Printer::class)->setSettings($settings ?? new TestSettings());
    }

    protected function getGraphQLSchemaBuilder(): SchemaBuilderWrapper {
        // Wrap
        $builder = $this->app->resolved(SchemaBuilder::class)
            ? $this->app->make(SchemaBuilder::class)
            : null;

        if (!($builder instanceof SchemaBuilderWrapper)) {
            $this->app->extend(
                SchemaBuilder::class,
                static function (SchemaBuilder $builder): SchemaBuilder {
                    return new SchemaBuilderWrapper($builder);
                },
            );
        }

        // Instance
        $builder = $this->app->make(SchemaBuilder::class);

        assert($builder instanceof SchemaBuilderWrapper);

        // Return
        return $builder;
    }
    // </editor-fold>
}
