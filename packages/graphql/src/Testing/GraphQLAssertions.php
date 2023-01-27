<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\Result;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Printer;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Statistics;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;
use LastDragon_ru\LaraASP\Testing\Utils\Args;
use Nuwave\Lighthouse\Schema\SchemaBuilder;
use Nuwave\Lighthouse\Testing\MocksResolvers;
use Nuwave\Lighthouse\Testing\TestSchemaProvider;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

use function array_combine;
use function assert;
use function is_string;

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
        GraphQLExpectedSchema|Result|Schema|SplFileInfo|string $expected,
        Result|Schema|SplFileInfo|string $schema,
        string $message = '',
    ): void {
        // GraphQL
        $output   = $expected;
        $settings = null;

        if ($output instanceof GraphQLExpectedSchema) {
            $settings = $output->getSettings();
            $output   = $output->getSchema();
        }

        if ($output instanceof Result || $output instanceof Schema) {
            $output = (string) $this->printGraphQLSchema($output, $settings);
        } else {
            $output = Args::content($output);
        }

        $actual = $this->printGraphQLSchema($schema, $settings);

        self::assertEquals($output, (string) $actual, $message);

        // Prepare
        if (!($expected instanceof GraphQLExpectedSchema)) {
            $expected = new GraphQLExpectedSchema($expected);
        }

        // Expectation
        $this->assertGraphQLExpectation($expected, $actual);
    }

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
     * Compares two GraphQL types (full).
     */
    public function assertGraphQLSchemaTypeEquals(
        GraphQLExpectedType|Result|Type|SplFileInfo|string $expected,
        Result|Type|string $type,
        Schema|SplFileInfo|string $schema = null,
        string $message = '',
    ): void {
        // Schema
        if ($schema instanceof SplFileInfo || is_string($schema)) {
            $schema = $this->getGraphQLSchema($schema);
        } elseif ($schema === null) {
            $schema = $this->getDefaultGraphQLSchema();
        } else {
            // empty
        }

        // GraphQL
        $output   = $expected;
        $settings = null;

        if ($output instanceof GraphQLExpectedType) {
            $settings = $output->getSettings();
            $output   = $output->getType();
        }

        if ($output instanceof Result || $output instanceof Type) {
            $output = (string) $this->printGraphQLSchemaType($schema, $output, $settings);
        } else {
            $output = Args::content($output);
        }

        $actual = $this->printGraphQLSchemaType($schema, $type, $settings);

        self::assertEquals($output, (string) $actual, $message);

        // Expectation
        if (!($expected instanceof GraphQLExpectedType)) {
            $expected = new GraphQLExpectedType($expected);
        }

        $this->assertGraphQLExpectation($expected, $actual);
    }

    /**
     * Compares two GraphQL types.
     */
    public function assertGraphQLTypeEquals(
        GraphQLExpectedType|Result|Type|SplFileInfo|string $expected,
        Result|Type $type,
        string $message = '',
    ): void {
        // GraphQL
        $output   = $expected;
        $settings = null;

        if ($output instanceof GraphQLExpectedType) {
            $settings = $output->getSettings();
            $output   = $output->getType();
        }

        if ($output instanceof Result || $output instanceof Type) {
            $output = (string) $this->printGraphQLType($output, $settings);
        } else {
            $output = Args::content($output);
        }

        $actual = $this->printGraphQLType($type, $settings);

        self::assertEquals($output, (string) $actual, $message);

        // Expectation
        if (!($expected instanceof GraphQLExpectedType)) {
            $expected = new GraphQLExpectedType($expected);
        }

        $this->assertGraphQLExpectation($expected, $actual);
    }

    private function assertGraphQLExpectation(
        GraphQLExpected $expected,
        Statistics $actual,
    ): void {
        // Used types
        $usedTypes = $expected->getUsedTypes();

        if ($usedTypes !== null) {
            self::assertEquals(
                array_combine($usedTypes, $usedTypes),
                $actual->getUsedTypes(),
                'Used Types not match.',
            );
        }

        // Used directives
        $usedDirectives = $expected->getUsedDirectives();

        if ($usedDirectives !== null) {
            self::assertEquals(
                array_combine($usedDirectives, $usedDirectives),
                $actual->getUsedDirectives(),
                'Used Directives not match.',
            );
        }
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    protected function useGraphQLSchema(SplFileInfo|string $schema): static {
        $schema   = Args::content($schema);
        $provider = new TestSchemaProvider($schema);

        $this->getGraphQLSchemaBuilder()->setSchema($provider);

        return $this;
    }

    protected function getGraphQLSchema(SplFileInfo|string $schema): Schema {
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
        Result|Schema|SplFileInfo|string $schema,
        Settings $settings = null,
    ): Result {
        if ($schema instanceof Result) {
            return $schema;
        }

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
        Schema $schema,
        Result|Type|string $type,
        Settings $settings = null,
    ): Result {
        return $type instanceof Type || is_string($type)
            ? $this->getGraphQLSchemaPrinter($settings)->printSchemaType($schema, $type)
            : $type;
    }

    protected function printDefaultGraphQLSchema(Settings $settings = null): Result {
        $schema  = $this->useDefaultGraphQLSchema()->getGraphQLSchemaBuilder()->schema();
        $printed = $this->getGraphQLSchemaPrinter($settings)->printSchema($schema);

        return $printed;
    }

    protected function printGraphQLType(Result|Type $type, Settings $settings = null): Result {
        return $type instanceof Type
            ? $this->getGraphQLSchemaPrinter($settings)->printType($type)
            : $type;
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
