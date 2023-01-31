<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Testing;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Printer as PrinterContract;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Result;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Statistics;
use LastDragon_ru\LaraASP\GraphQLPrinter\Printer;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;
use LastDragon_ru\LaraASP\Testing\Utils\Args;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

use function array_combine;
use function is_string;

/**
 * @mixin TestCase
 */
trait GraphQLAssertions {
    // <editor-fold desc="Assertions">
    // =========================================================================
    /**
     * Compares two GraphQL schemas.
     */
    public function assertGraphQLSchemaEquals(
        GraphQLExpectedSchema|Schema|DocumentNode|SplFileInfo|string $expected,
        Result|Schema|DocumentNode|SplFileInfo|string $schema,
        string $message = '',
    ): void {
        // GraphQL
        $output   = $expected;
        $settings = null;

        if ($output instanceof GraphQLExpectedSchema) {
            $settings = $output->getSettings();
            $output   = $output->getSchema();
        }

        if ($output instanceof Schema || $output instanceof DocumentNode) {
            $output = (string) $this->printGraphQLSchema($output, $settings);
        } else {
            $output = Args::content($output);
        }

        if (!($schema instanceof Result)) {
            $schema = $this->printGraphQLSchema($schema, $settings);
        }

        self::assertEquals($output, (string) $schema, $message);

        // Prepare
        if (!($expected instanceof GraphQLExpectedSchema)) {
            $expected = new GraphQLExpectedSchema($expected);
        }

        // Expectation
        $this->assertGraphQLExpectation($expected, $schema);
    }

    /**
     * Compares two GraphQL types (full).
     */
    public function assertGraphQLSchemaTypeEquals(
        GraphQLExpectedType|Type|SplFileInfo|string $expected,
        Result|Type|string $type,
        Schema|DocumentNode|SplFileInfo|string $schema,
        string $message = '',
    ): void {
        // Schema
        if (!($schema instanceof Schema)) {
            $schema = $this->getGraphQLSchema($schema);
        }

        // GraphQL
        $output   = $expected;
        $settings = null;

        if ($output instanceof GraphQLExpectedType) {
            $settings = $output->getSettings();
            $output   = $output->getType();
        }

        if ($output instanceof Type) {
            $output = (string) $this->printGraphQLSchemaType($schema, $output, $settings);
        } else {
            $output = Args::content($output);
        }

        if (!($type instanceof Result)) {
            $type = $this->printGraphQLSchemaType($schema, $type, $settings);
        }

        self::assertEquals($output, (string) $type, $message);

        // Expectation
        if (!($expected instanceof GraphQLExpectedType)) {
            $expected = new GraphQLExpectedType($expected);
        }

        $this->assertGraphQLExpectation($expected, $type);
    }

    /**
     * Compares two GraphQL types.
     */
    public function assertGraphQLTypeEquals(
        GraphQLExpectedType|Type|SplFileInfo|string $expected,
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

        if ($output instanceof Type) {
            $output = (string) $this->printGraphQLType($output, $settings);
        } else {
            $output = Args::content($output);
        }

        if (!($type instanceof Result)) {
            $type = $this->printGraphQLType($type, $settings);
        }

        self::assertEquals($output, (string) $type, $message);

        // Expectation
        if (!($expected instanceof GraphQLExpectedType)) {
            $expected = new GraphQLExpectedType($expected);
        }

        $this->assertGraphQLExpectation($expected, $type);
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
    protected function getGraphQLSchema(Schema|DocumentNode|SplFileInfo|string $schema): Schema {
        if ($schema instanceof SplFileInfo || is_string($schema)) {
            $schema = Args::content($schema);
        }

        if (!($schema instanceof Schema)) {
            $schema = BuildSchema::build($schema);
        }

        return $schema;
    }

    protected function printGraphQLSchema(
        Schema|DocumentNode|SplFileInfo|string $schema,
        Settings $settings = null,
    ): Result {
        $schema = $this->getGraphQLSchema($schema);
        $result = $this->getGraphQLSchemaPrinter($settings)->printSchema($schema);

        return $result;
    }

    protected function printGraphQLSchemaType(
        Schema|DocumentNode|SplFileInfo|string $schema,
        Type|string $type,
        Settings $settings = null,
    ): Result {
        $schema = $this->getGraphQLSchema($schema);
        $result = $this->getGraphQLSchemaPrinter($settings)->printSchemaType($schema, $type);

        return $result;
    }

    protected function printGraphQLType(Type $type, Settings $settings = null): Result {
        return $this->getGraphQLSchemaPrinter($settings)->printType($type);
    }

    protected function getGraphQLSchemaPrinter(Settings $settings = null): PrinterContract {
        return new Printer($settings ?? new TestSettings());
    }
    // </editor-fold>
}
