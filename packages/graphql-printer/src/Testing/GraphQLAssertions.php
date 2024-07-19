<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Testing;

use Closure;
use GraphQL\Language\AST\Node;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Argument;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\EnumValueDefinition;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
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
            static function (PrinterContract $printer, mixed $printable): Result {
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
            static function (PrinterContract $printer, mixed $printable): Result {
                return $printer->export($printable);
            },
        );

        // Expectation
        if ($expected instanceof GraphQLExpected) {
            $this->assertGraphQLExpectation($expected, $actual);
        }
    }

    /**
     * @param Closure(PrinterContract, Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema): Result $print
     */
    private function assertGraphQLResult(
        Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema|GraphQLExpected|SplFileInfo|string $expected,
        Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema|Result|SplFileInfo|string $actual,
        string $message,
        Closure $print,
    ): Result {
        // GraphQL
        $output   = $expected;
        $schema   = null;
        $settings = null;

        if ($output instanceof GraphQLExpected) {
            $settings = $output->getSettings();
            $schema   = $output->getSchema();
            $output   = $output->getPrintable();
        }

        // Printer
        $printer = $this->getGraphQLPrinter($settings);

        if ($schema !== null) {
            $printer = $printer->setSchema($schema);
        }

        // Compare
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
    protected function getGraphQLPrinter(Settings $settings = null): PrinterContract {
        return new Printer($settings ?? new TestSettings());
    }
    // </editor-fold>
}
