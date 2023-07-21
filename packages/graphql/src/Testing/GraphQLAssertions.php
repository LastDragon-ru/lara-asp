<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Printer;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Result;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
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

    use PrinterGraphQLAssertions {
        printGraphQLSchema as private printerPrintGraphQLSchema;
    }

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
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    protected function useGraphQLSchema(Schema|DocumentNode|SplFileInfo|string $schema): static {
        $schema   = $schema instanceof Schema || $schema instanceof DocumentNode
            ? $this->printerPrintGraphQLSchema($schema)
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

            return $this->getGraphQLSchemaPrinter($settings)->printSchema($schema);
        } finally {
            $this->useDefaultGraphQLSchema();
        }
    }

    protected function printGraphQLSchemaType(
        Schema|DocumentNode|SplFileInfo|string $schema,
        Type|string $type,
        Settings $settings = null,
    ): Result {
        try {
            if (!($schema instanceof Schema)) {
                $schema = $this->useGraphQLSchema($schema)->getGraphQLSchemaBuilder()->schema();
            }

            return $this->getGraphQLSchemaPrinter($settings)->printSchemaType($schema, $type);
        } finally {
            $this->useDefaultGraphQLSchema();
        }
    }

    protected function printDefaultGraphQLSchema(Settings $settings = null): Result {
        $schema  = $this->useDefaultGraphQLSchema()->getGraphQLSchemaBuilder()->schema();
        $printed = $this->getGraphQLSchemaPrinter($settings)->printSchema($schema);

        return $printed;
    }

    protected function getGraphQLSchemaPrinter(Settings $settings = null): Printer {
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
