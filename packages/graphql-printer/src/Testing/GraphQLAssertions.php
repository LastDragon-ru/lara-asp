<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Testing;

use Closure;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Argument;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\EnumValueDefinition;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Printer as PrinterContract;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Result;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Statistics;
use LastDragon_ru\LaraASP\GraphQLPrinter\Printer;
use LastDragon_ru\LaraASP\Testing\Utils\Args;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

use function array_combine;
use function is_string;

// @phpcs:disable Generic.Files.LineLength.TooLong

/**
 * @mixin TestCase
 */
trait GraphQLAssertions {
    // <editor-fold desc="Assertions">
    // =========================================================================
    /**
     * Prints and compares two GraphQL schemas/types/nodes/etc.
     */
    public function assertGraphQLPrintableEquals(
        Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema|GraphQLExpected|SplFileInfo|string $expected,
        Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema|Result|SplFileInfo|string $actual,
        string $message = '',
    ): void {
        // Printed
        $actual = $this->assertGraphQLResult(
            $expected,
            $actual,
            $message,
            static function (Printer $printer, mixed $printable): Result {
                return $printer->print($printable);
            },
        );

        // Expectation
        if ($expected instanceof GraphQLExpected) {
            $this->assertGraphQLExpectation($expected, $actual);
        }
    }

    /**
     * Exports and compares two GraphQL schemas/types/nodes/etc.
     */
    public function assertGraphQLExportableEquals(
        Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema|GraphQLExpected|SplFileInfo|string $expected,
        Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema|Result|SplFileInfo|string $actual,
        string $message = '',
    ): void {
        // Printed
        $actual = $this->assertGraphQLResult(
            $expected,
            $actual,
            $message,
            static function (Printer $printer, mixed $printable): Result {
                return $printer->export($printable);
            },
        );

        // Expectation
        if ($expected instanceof GraphQLExpected) {
            $this->assertGraphQLExpectation($expected, $actual);
        }
    }

    /**
     * Compares two GraphQL schemas.
     *
     * @deprecated 4.4.0 Please use {@see self::assertGraphQLPrintableEquals()}/{@see self::assertGraphQLExportableEquals()} instead.
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
     *
     * @deprecated 4.4.0 Please use {@see self::assertGraphQLExportableEquals()} instead.
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
     *
     * @deprecated 4.4.0 Please use {@see self::assertGraphQLPrintableEquals()} instead.
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

    /**
     * Compares two GraphQL nodes.
     *
     * @deprecated 4.4.0 Please use {@see self::assertGraphQLPrintableEquals()} instead.
     */
    public function assertGraphQLNodeEquals(
        GraphQLExpectedNode|Node|SplFileInfo|string $expected,
        Result|Node $type,
        string $message = '',
    ): void {
        // GraphQL
        $output   = $expected;
        $settings = null;

        if ($output instanceof GraphQLExpectedNode) {
            $settings = $output->getSettings();
            $output   = $output->getNode();
        }

        if ($output instanceof Node) {
            $output = (string) $this->printGraphQLNode($output, $settings);
        } else {
            $output = Args::content($output);
        }

        if (!($type instanceof Result)) {
            $type = $this->printGraphQLNode($type, $settings);
        }

        self::assertEquals($output, (string) $type, $message);

        // Expectation
        if (!($expected instanceof GraphQLExpectedNode)) {
            $expected = new GraphQLExpectedNode($expected);
        }

        $this->assertGraphQLExpectation($expected, $type);
    }

    /**
     * @param Closure(Printer, Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema): Result $print
     */
    private function assertGraphQLResult(
        Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema|GraphQLExpected|SplFileInfo|string $expected,
        Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema|Result|SplFileInfo|string $actual,
        string $message,
        Closure $print,
    ): Result {
        // GraphQL
        $output   = $expected;
        $settings = null;

        if ($output instanceof GraphQLExpected) {
            $settings = $output->getSettings();
            $output   = $output->getPrintable();
        }

        // Compare
        $printer = $this->getGraphQLPrinter($settings);

        if (!($output instanceof SplFileInfo) && !is_string($output)) {
            $output = (string) $print($printer, $output);
        } else {
            $output = Args::content($output);
        }

        if ($actual instanceof SplFileInfo || is_string($actual)) {
            $actual = Parser::parse(Args::content($actual));
        }

        if (!($actual instanceof Result)) {
            $actual = $print($printer, $actual);
        } else {
            // empty
        }

        self::assertEquals($output, (string) $actual, $message);

        return $actual;
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
    /**
     * @deprecated 4.4.0 Please use {@see BuildSchema::build()} instead.
     */
    protected function getGraphQLSchema(Schema|DocumentNode|SplFileInfo|string $schema): Schema {
        if ($schema instanceof SplFileInfo || is_string($schema)) {
            $schema = Args::content($schema);
        }

        if (!($schema instanceof Schema)) {
            $schema = BuildSchema::build($schema);
        }

        return $schema;
    }

    /**
     * @deprecated 4.4.0 Method will be removed in the next major version.
     */
    protected function printGraphQLSchema(
        Schema|DocumentNode|SplFileInfo|string $schema,
        Settings $settings = null,
    ): Result {
        $schema = $this->getGraphQLSchema($schema);
        $result = $this->getGraphQLPrinter($settings)->printSchema($schema);

        return $result;
    }

    /**
     * @deprecated 4.4.0 Method will be removed in the next major version.
     */
    protected function printGraphQLSchemaType(
        Schema|DocumentNode|SplFileInfo|string $schema,
        Type|string $type,
        Settings $settings = null,
    ): Result {
        $schema = $this->getGraphQLSchema($schema);
        $result = $this->getGraphQLPrinter($settings)->printSchemaType($schema, $type);

        return $result;
    }

    /**
     * @deprecated 4.4.0 Method will be removed in the next major version.
     */
    protected function printGraphQLType(Type $type, Settings $settings = null): Result {
        return $this->getGraphQLPrinter($settings)->printType($type);
    }

    /**
     * @deprecated 4.4.0 Method will be removed in the next major version.
     */
    protected function printGraphQLNode(Node $node, Settings $settings = null): Result {
        return $this->getGraphQLPrinter($settings)->printNode($node);
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
        return new Printer($settings ?? new TestSettings());
    }
    // </editor-fold>
}
