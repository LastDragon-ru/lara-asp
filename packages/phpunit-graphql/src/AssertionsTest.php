<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\GraphQL;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\Node;
use GraphQL\Type\Schema;
use LastDragon_ru\GraphQLPrinter\Contracts\Printer;
use LastDragon_ru\GraphQLPrinter\Contracts\Result;
use LastDragon_ru\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\GraphQLPrinter\Contracts\Statistics;
use LastDragon_ru\PhpUnit\GraphQL\Package\TestCase;
use Mockery;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversTrait;

/**
 * @internal
 */
#[CoversTrait(Assertions::class)]
final class AssertionsTest extends TestCase {
    public function testAssertGraphQLPrintableEquals(): void {
        $printable = Mockery::mock(Node::class);
        $expected  = new Expected('result');
        $result    = Mockery::mock(Result::class);
        $result
            ->shouldReceive('__toString')
            ->once()
            ->andReturn('result');

        $printer = Mockery::mock(Printer::class);
        $printer
            ->shouldReceive('print')
            ->once()
            ->andReturn($result);

        $instance = Mockery::mock(GraphQLAssertionsTest_TestCase::class);
        $instance->shouldAllowMockingProtectedMethods();
        $instance->makePartial();
        $instance
            ->shouldReceive('getGraphQLPrinter')
            ->once()
            ->andReturn($printer);
        $instance
            ->shouldReceive('assertGraphQLExpectation')
            ->with($expected, Mockery::andAnyOtherArgs())
            ->once()
            ->andReturns();

        $instance->assertGraphQLPrintableEquals($expected, $printable);
    }

    public function testAssertGraphQLExportableEquals(): void {
        $exportable = Mockery::mock(Node::class);
        $expected   = new Expected('result');
        $result     = Mockery::mock(Result::class);
        $result
            ->shouldReceive('__toString')
            ->once()
            ->andReturn('result');

        $printer = Mockery::mock(Printer::class);
        $printer
            ->shouldReceive('export')
            ->once()
            ->andReturn($result);

        $instance = Mockery::mock(GraphQLAssertionsTest_TestCase::class);
        $instance->shouldAllowMockingProtectedMethods();
        $instance->makePartial();
        $instance
            ->shouldReceive('getGraphQLPrinter')
            ->once()
            ->andReturn($printer);
        $instance
            ->shouldReceive('assertGraphQLExpectation')
            ->with($expected, Mockery::andAnyOtherArgs())
            ->once()
            ->andReturns();

        $instance->assertGraphQLExportableEquals($expected, $exportable);
    }

    public function testAssertGraphQLResultShouldRespectSettingsAndSchema(): void {
        $node     = Mockery::mock(Node::class);
        $schema   = Mockery::mock(Schema::class);
        $settings = Mockery::mock(Settings::class);
        $expected = new Expected($node, settings: $settings, schema: $schema);

        $result = Mockery::mock(Result::class);
        $result
            ->shouldReceive('__toString')
            ->twice()
            ->andReturn('result');

        $printer = Mockery::mock(Printer::class);
        $printer
            ->shouldReceive('setSchema')
            ->with($schema)
            ->once()
            ->andReturnSelf();

        $instance = Mockery::mock(GraphQLAssertionsTest_TestCase::class);
        $instance->shouldAllowMockingProtectedMethods();
        $instance->makePartial();
        $instance
            ->shouldReceive('getGraphQLPrinter')
            ->with($settings)
            ->once()
            ->andReturn($printer);

        $print = Mockery::spy(static fn () => null);
        $print
            ->shouldReceive('__invoke')
            ->twice()
            ->andReturn($result);

        $instance->assertGraphQLResult($expected, $node, 'message', $print(...));
    }

    public function testAssertGraphQLResultShouldParseActual(): void {
        $result = Mockery::mock(Result::class);
        $result
            ->shouldReceive('__toString')
            ->once()
            ->andReturn('result');

        $instance = Mockery::mock(GraphQLAssertionsTest_TestCase::class);
        $instance->shouldAllowMockingProtectedMethods();
        $instance->makePartial();

        $print = Mockery::mock(static fn () => null);
        $print
            ->shouldReceive('__invoke')
            ->with(Mockery::type(Printer::class), Mockery::type(DocumentNode::class))
            ->once()
            ->andReturn($result);

        $instance->assertGraphQLResult('result', 'directive @test on SCALAR', 'message', $print(...));
    }

    public function testAssertGraphQLExpectation(): void {
        $actual = Mockery::mock(Statistics::class);
        $actual
            ->shouldReceive('getUsedTypes')
            ->once()
            ->andReturn(['A' => 'A']);
        $actual
            ->shouldReceive('getUsedDirectives')
            ->once()
            ->andReturn(['@a' => '@a']);
        $expected = new Expected('expected', ['A'], ['@a']);

        $instance = Mockery::mock(GraphQLAssertionsTest_TestCase::class);
        $instance->makePartial();

        $instance->assertGraphQLExpectation($expected, $actual);
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class GraphQLAssertionsTest_TestCase extends Assert {
    use Assertions {
        assertGraphQLExpectation as public;
        assertGraphQLResult as public;
    }
}
