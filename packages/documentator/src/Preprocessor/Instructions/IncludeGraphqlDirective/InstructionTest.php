<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeGraphqlDirective;

use GraphQL\Language\Parser;
use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\DependencyIsMissing;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetIsNotDirective;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\DirectiveResolver;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Printer as PrinterContract;
use LastDragon_ru\LaraASP\GraphQLPrinter\Printer;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Instruction::class)]
final class InstructionTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    public function testProcess(): void {
        $directive = <<<'GRAPHQL'
            directive @test
            on
                | SCALAR
            GRAPHQL;

        $this->override(PrinterContract::class, static function () use ($directive): PrinterContract {
            $resolver = Mockery::mock(DirectiveResolver::class);
            $resolver
                ->shouldReceive('getDefinition')
                ->with('test')
                ->once()
                ->andReturn(
                    Parser::directiveDefinition($directive),
                );

            return (new Printer())->setDirectiveResolver($resolver);
        });

        $instance = Container::getInstance()->make(Instruction::class);
        $actual   = $instance->process('path/to/file.md', '@test');

        self::assertEquals(
            <<<MARKDOWN
            ```graphql
            {$directive}
            ```
            MARKDOWN,
            $actual,
        );
    }

    public function testProcessNoPrinter(): void {
        unset(Container::getInstance()[PrinterContract::class]);

        $path     = 'path/to/file.md';
        $target   = '@test';
        $instance = Container::getInstance()->make(Instruction::class);

        self::expectExceptionObject(
            new DependencyIsMissing($path, $target, PrinterContract::class),
        );

        $instance->process($path, $target);
    }

    public function testProcessNoDirective(): void {
        $this->override(PrinterContract::class, static function (): PrinterContract {
            $resolver = Mockery::mock(DirectiveResolver::class);
            $resolver
                ->shouldReceive('getDefinition')
                ->with('test')
                ->once()
                ->andReturn(
                    null,
                );

            return (new Printer())->setDirectiveResolver($resolver);
        });

        $path     = 'path/to/file.md';
        $target   = '@test';
        $instance = Container::getInstance()->make(Instruction::class);

        self::expectExceptionObject(
            new TargetIsNotDirective($path, $target),
        );

        $instance->process($path, $target);
    }

    public function testProcessNoDirectiveResolver(): void {
        $this->override(PrinterContract::class, static function (): PrinterContract {
            return (new Printer())->setDirectiveResolver(null);
        });

        $path     = 'path/to/file.md';
        $target   = '@test';
        $instance = Container::getInstance()->make(Instruction::class);

        self::expectExceptionObject(
            new TargetIsNotDirective($path, $target),
        );

        $instance->process($path, $target);
    }
}
