<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeArtisan;

use Illuminate\Contracts\Console\Kernel;
use LastDragon_ru\LaraASP\Core\Application\ApplicationResolver;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Node;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Nop;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeArtisan\Exceptions\ArtisanCommandFailed;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\WithProcessor;
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
    use WithProcessor;

    public function testInvoke(): void {
        $fs       = $this->getFileSystem(__DIR__);
        $file     = $fs->getFile(__FILE__);
        $params   = new Parameters('command to execute');
        $expected = 'result';
        $command  = $params->target;
        $context  = new Context($file, Mockery::mock(Document::class), new Node(), new Nop());
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

        self::assertSame($expected, $this->getProcessorResult($fs, ($instance)($context, $params)));
    }

    public function testInvokeFailed(): void {
        $fs       = $this->getFileSystem(__DIR__);
        $file     = $fs->getFile(__FILE__);
        $node     = new class() extends Node {
            #[Override]
            public function getDestination(): string {
                return 'command to execute';
            }
        };
        $params   = new Parameters($node->getDestination());
        $command  = $params->target;
        $context  = new Context($file, Mockery::mock(Document::class), $node, new Nop());
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
                'Artisan command `%s` exited with status code `%s` (`%s` line).',
                $command,
                Command::FAILURE,
                'unknown',
            ),
        );

        $this->getProcessorResult($fs, ($instance)($context, $params));
    }

    public function testGetCommand(): void {
        $fs       = $this->getFileSystem(__DIR__);
        $file     = $fs->getFile(__FILE__);
        $params   = new Parameters('artisan:command $directory {$directory} "{$directory}" $file {$file} "{$file}"');
        $command  = $params->target;
        $context  = new Context($file, Mockery::mock(Document::class), new Node(), new Nop());
        $instance = new class (Mockery::mock(ApplicationResolver::class)) extends Instruction {
            #[Override]
            public function getCommand(Context $context, string $target, Parameters $parameters): string {
                return parent::getCommand($context, $target, $parameters);
            }
        };

        self::assertEquals(
            sprintf(
                'artisan:command $directory %1$s "%1$s" $file %2$s "%2$s"',
                $file->getDirectoryPath(),
                $file->getPath(),
            ),
            $instance->getCommand($context, $command, $params),
        );
    }
}
