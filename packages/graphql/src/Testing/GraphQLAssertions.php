<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Printer as PrinterContract;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Printer;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\GraphQLAssertions as PrinterGraphQLAssertions;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\GraphQLExpected;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\TestSettings;
use LastDragon_ru\LaraASP\Testing\Utils\Args;
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
    public function assertCurrentGraphQLSchemaEquals(
        GraphQLExpected|SplFileInfo|string $expected,
        string $message = '',
    ): void {
        self::assertGraphQLPrintableEquals(
            $expected,
            $this->getCurrentGraphQLSchema(),
            $message,
        );
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    protected function useGraphQLSchema(Schema|DocumentNode|SplFileInfo|string $schema): static {
        $schema   = $schema instanceof Schema || $schema instanceof DocumentNode
            ? (string) $this->getGraphQLPrinter()->print($schema)
            : Args::content($schema);
        $provider = new TestSchemaProvider($schema);

        $this->getGraphQLSchemaBuilder()->setSchema($provider);

        return $this;
    }

    protected function resetGraphQLSchema(): static {
        $this->getGraphQLSchemaBuilder()->setSchema(null);

        return $this;
    }

    protected function getCurrentGraphQLSchema(): Schema {
        return $this->getGraphQLSchemaBuilder()->schema();
    }

    /**
     * @return PrinterContract&Printer
     */
    protected function getGraphQLPrinter(Settings $settings = null): PrinterContract {
        $printer = $this->app->make(PrinterContract::class)->setSettings($settings ?? new TestSettings());

        assert($printer instanceof Printer);

        return $printer;
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
