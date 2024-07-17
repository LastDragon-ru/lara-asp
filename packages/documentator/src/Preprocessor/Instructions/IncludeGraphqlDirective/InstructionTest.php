<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeGraphqlDirective;

// @phpcs:disable Generic.Files.LineLength.TooLong

use ArrayAccess;
use GraphQL\Language\Parser;
use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\DependencyIsMissing;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeGraphqlDirective\Exceptions\TargetIsNotDirective;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\ProcessorHelper;
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
    public function testInvoke(): void {
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
                ->atLeast()
                ->once()
                ->andReturn(
                    Parser::directiveDefinition($directive),
                );

            return (new Printer())->setDirectiveResolver($resolver);
        });

        $root     = Mockery::mock(Directory::class);
        $file     = Mockery::mock(File::class);
        $params   = new Parameters('...');
        $target   = '@test';
        $context  = new Context($root, $file, $target, '{...}');
        $instance = $this->app()->make(Instruction::class);
        $actual   = ProcessorHelper::runInstruction($instance, $context, $target, $params);

        self::assertEquals(
            <<<MARKDOWN
            ```graphql
            {$directive}
            ```
            MARKDOWN,
            $actual,
        );
    }

    public function testInvokeNoPrinter(): void {
        // Reset
        if ($this->app() instanceof ArrayAccess) {
            unset($this->app()[PrinterContract::class]);
        }

        // Test
        $root     = new Directory(Path::normalize(__DIR__), false);
        $file     = new File(Path::normalize(__FILE__), false);
        $params   = new Parameters('...');
        $target   = '@test';
        $context  = new Context($root, $file, $target, '{...}');
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new DependencyIsMissing($context, PrinterContract::class),
        );

        ProcessorHelper::runInstruction($instance, $context, $target, $params);
    }

    public function testInvokeNoDirective(): void {
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

        $root     = new Directory(Path::normalize(__DIR__), false);
        $file     = new File(Path::normalize(__FILE__), false);
        $params   = new Parameters('...');
        $target   = '@test';
        $context  = new Context($root, $file, $target, '{...}');
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new TargetIsNotDirective($context),
        );

        ProcessorHelper::runInstruction($instance, $context, $target, $params);
    }

    public function testInvokeNoDirectiveResolver(): void {
        $this->override(PrinterContract::class, static function (): PrinterContract {
            return (new Printer())->setDirectiveResolver(null);
        });

        $root     = new Directory(Path::normalize(__DIR__), false);
        $file     = new File(Path::normalize(__FILE__), false);
        $params   = new Parameters('...');
        $target   = '@test';
        $context  = new Context($root, $file, $target, '{...}');
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new TargetIsNotDirective($context),
        );

        ProcessorHelper::runInstruction($instance, $context, $target, $params);
    }
}
