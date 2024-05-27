<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeArtisan;

use Illuminate\Contracts\Console\Kernel;
use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeArtisan\Exceptions\ArtisanCommandFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\ProcessorHelper;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Mockery\MockInterface;
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
        $root     = new Directory(Path::normalize(__DIR__), false);
        $file     = new File(Path::normalize(__FILE__), false);
        $params   = null;
        $expected = 'result';
        $command  = 'command to execute';
        $context  = new Context($root, $file, $command, $params);
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
        $root     = new Directory(Path::normalize(__DIR__), false);
        $file     = new File(Path::normalize(__FILE__), false);
        $params   = null;
        $command  = 'command to execute';
        $context  = new Context($root, $file, $command, $params);
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
                $context->file->getRelativePath($context->root),
            ),
        );

        ProcessorHelper::runInstruction($instance, $context, $command, $params);
    }
}
