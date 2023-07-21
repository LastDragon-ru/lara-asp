<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Printer as PrinterContract;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Result;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Printer;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\GraphQLAssertions as PrinterGraphQLAssertions;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\GraphQLExpectedSchema;
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
     * Compares GraphQL schema with default (application) schema.
     */
    public function assertDefaultGraphQLSchemaEquals(
        GraphQLExpectedSchema|SplFileInfo|string $expected,
        string $message = '',
    ): void {
        self::assertGraphQLSchemaEquals(
            $expected,
            $this->getDefaultGraphQLSchema(),
            $message,
        );
    }

    /**
     * Compares two GraphQL schemas.
     */
    public function assertGraphQLSchemaEquals(
        GraphQLExpectedSchema|SplFileInfo|string $expected,
        Schema|DocumentNode|SplFileInfo|string $schema,
        string $message = '',
    ): void {
        self::assertGraphQLPrintableEquals(
            $expected,
            $this->getGraphQLSchema($schema),
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

    protected function getGraphQLSchema(Schema|DocumentNode|SplFileInfo|string $schema): Schema {
        try {
            return $this->useGraphQLSchema($schema)->getGraphQLSchemaBuilder()->schema();
        } finally {
            $this->useDefaultGraphQLSchema();
        }
    }

    protected function useDefaultGraphQLSchema(): static {
        $this->getGraphQLSchemaBuilder()->setSchema(null);

        return $this;
    }

    protected function getDefaultGraphQLSchema(): Schema {
        return $this->useDefaultGraphQLSchema()->getGraphQLSchemaBuilder()->schema();
    }

    protected function printGraphQLSchema(
        Schema|DocumentNode|SplFileInfo|string $schema,
        Settings $settings = null,
    ): Result {
        try {
            if (!($schema instanceof Schema)) {
                $schema = $this->useGraphQLSchema($schema)->getGraphQLSchemaBuilder()->schema();
            }

            return $this->getGraphQLPrinter($settings)->printSchema($schema);
        } finally {
            $this->useDefaultGraphQLSchema();
        }
    }

    /**
     * @deprecated 4.4.0 Method will be removed in the next major version.
     */
    protected function printGraphQLSchemaType(
        Schema|DocumentNode|SplFileInfo|string $schema,
        Type|string $type,
        Settings $settings = null,
    ): Result {
        try {
            if (!($schema instanceof Schema)) {
                $schema = $this->useGraphQLSchema($schema)->getGraphQLSchemaBuilder()->schema();
            }

            return $this->getGraphQLPrinter($settings)->printSchemaType($schema, $type);
        } finally {
            $this->useDefaultGraphQLSchema();
        }
    }

    protected function printDefaultGraphQLSchema(Settings $settings = null): Result {
        $schema  = $this->useDefaultGraphQLSchema()->getGraphQLSchemaBuilder()->schema();
        $printed = $this->getGraphQLPrinter($settings)->printSchema($schema);

        return $printed;
    }

    /**
     * @deprecated 4.4.0 Please use {@see self::getGraphQLPrinter()} instead.
     */
    protected function getGraphQLSchemaPrinter(Settings $settings = null): PrinterContract {
        return $this->getGraphQLPrinter($settings);
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
