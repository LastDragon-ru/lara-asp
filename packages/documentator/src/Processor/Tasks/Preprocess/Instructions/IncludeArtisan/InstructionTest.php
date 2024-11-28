<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeArtisan;

use Illuminate\Contracts\Console\Kernel;
use LastDragon_ru\LaraASP\Core\Application\ApplicationResolver;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Nop;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Reference\Block;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeArtisan\Exceptions\ArtisanCommandFailed;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\ProcessorHelper;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

/**
 * @internal
 */
#[CoversClass(Instruction::class)]
final class InstructionTest extends TestCase {
    public function testInvoke(): void {
        $root     = new Directory((new DirectoryPath(__DIR__))->getNormalizedPath(), false);
        $file     = new File((new FilePath(__FILE__))->getNormalizedPath(), false);
        $params   = new Parameters('...');
        $expected = 'result';
        $command  = 'command to execute';
        $context  = new Context($root, $file, new Document(''), new Block(), new Nop());
        $instance = $this->app()->make(Instruction::class);

        $this->override(Kernel::class, static function (MockInterface $mock) use ($command, $expected): void {
            $mock
                ->shouldReceive('addCommands')
                ->atLeast()
                ->once()
                ->andReturns();
            $mock
                ->shouldReceive('addCommandPaths')
                ->atLeast()
                ->once()
                ->andReturns();
            $mock
                ->shouldReceive('addCommandRoutePaths')
                ->atLeast()
                ->once()
                ->andReturns();
            $mock
                ->shouldReceive('handle')
                ->withArgs(
                    static function (InputInterface $input) use ($command): bool {
                        return (string) $input === $command;
                    },
                )
                ->once()
                ->andReturnUsing(
                    static function (InputInterface $input, OutputInterface $output) use ($expected): int {
                        $output->writeln($expected);

                        return Command::SUCCESS;
                    },
                );
        });

        self::assertEquals($expected, ProcessorHelper::runInstruction($instance, $context, $command, $params));
    }

    public function testInvokeFailed(): void {
        $root     = new Directory((new DirectoryPath(__DIR__))->getNormalizedPath(), false);
        $file     = new File((new FilePath(__FILE__))->getNormalizedPath(), false);
        $node     = new class() extends Block {
            #[Override]
            public function getDestination(): string {
                return 'command to execute';
            }
        };
        $params   = new Parameters('...');
        $command  = $node->getDestination();
        $context  = new Context($root, $file, new Document(''), $node, new Nop());
        $instance = $this->app()->make(Instruction::class);

        $this->override(Kernel::class, static function (MockInterface $mock) use ($command): void {
            $mock
                ->shouldReceive('addCommands')
                ->atLeast()
                ->once()
                ->andReturns();
            $mock
                ->shouldReceive('addCommandPaths')
                ->atLeast()
                ->once()
                ->andReturns();
            $mock
                ->shouldReceive('addCommandRoutePaths')
                ->atLeast()
                ->once()
                ->andReturns();
            $mock
                ->shouldReceive('handle')
                ->withArgs(
                    static function (InputInterface $input) use ($command): bool {
                        return (string) $input === $command;
                    },
                )
                ->once()
                ->andReturnUsing(
                    static function (): int {
                        return Command::FAILURE;
                    },
                );
        });

        self::expectException(ArtisanCommandFailed::class);
        self::expectExceptionMessage(
            sprintf(
                'Artisan command `%s` exited with status code `%s` (in `%s`).',
                $command,
                Command::FAILURE,
                $context->root->getRelativePath($context->file),
            ),
        );

        ProcessorHelper::runInstruction($instance, $context, $command, $params);
    }

    public function testGetCommand(): void {
        $root     = new Directory((new DirectoryPath(__DIR__))->getNormalizedPath(), false);
        $file     = new File((new FilePath(__FILE__))->getNormalizedPath(), false);
        $params   = new Parameters('...');
        $command  = 'artisan:command $directory {$directory} "{$directory}" $file {$file} "{$file}"';
        $context  = new Context($root, $file, new Document(''), new Block(), new Nop());
        $instance = new class (Mockery::mock(ApplicationResolver::class)) extends Instruction {
            #[Override]
            public function getCommand(Context $context, string $target, Parameters $parameters): string {
                return parent::getCommand($context, $target, $parameters);
            }
        };

        self::assertEquals(
            sprintf(
                'artisan:command $directory %1$s "%1$s" $file %2$s "%2$s"',
                $file->getPath()->getDirectoryPath(),
                $file->getPath(),
            ),
            $instance->getCommand($context, $command, $params),
        );
    }
}
