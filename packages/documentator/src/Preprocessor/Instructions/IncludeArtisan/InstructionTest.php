<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeArtisan;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use Illuminate\Contracts\Console\Kernel;
use LastDragon_ru\LaraASP\Core\Application\ApplicationResolver;
use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeArtisan\Exceptions\ArtisanCommandFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\ProcessorHelper;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function dirname;
use function sprintf;

/**
 * @internal
 */
#[CoversClass(Instruction::class)]
final class InstructionTest extends TestCase {
    public function testInvoke(): void {
        $root     = new Directory(Path::normalize(__DIR__), false);
        $file     = new File(Path::normalize(__FILE__), false);
        $params   = new Parameters('...');
        $expected = 'result';
        $command  = 'command to execute';
        $context  = new Context($root, $file, $command, '{...}');
        $instance = $this->app()->make(Instruction::class);

        $this->override(Kernel::class, static function (MockInterface $mock) use ($command, $expected): void {
            if (InstalledVersions::satisfies(new VersionParser(), 'illuminate/contracts', '^11.0.0')) {
                // todo(documentator): Remove after https://github.com/LastDragon-ru/lara-asp/issues/143
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
            }

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
        $params   = new Parameters('...');
        $command  = 'command to execute';
        $context  = new Context($root, $file, $command, '{...}');
        $instance = $this->app()->make(Instruction::class);

        $this->override(Kernel::class, static function (MockInterface $mock) use ($command): void {
            // todo(documentator): Remove after https://github.com/LastDragon-ru/lara-asp/issues/143
            if (InstalledVersions::satisfies(new VersionParser(), 'illuminate/contracts', '^11.0.0')) {
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
            }

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

    public function testGetCommand(): void {
        $root     = new Directory(Path::normalize(__DIR__), false);
        $file     = new File(Path::normalize(__FILE__), false);
        $params   = new Parameters('...');
        $command  = 'artisan:command $directory {$directory} "{$directory}" $file {$file} "{$file}"';
        $context  = new Context($root, $file, $command, '{...}');
        $instance = new class (Mockery::mock(ApplicationResolver::class)) extends Instruction {
            #[Override]
            public function getCommand(Context $context, string $target, Parameters $parameters): string {
                return parent::getCommand($context, $target, $parameters);
            }
        };

        self::assertEquals(
            sprintf(
                'artisan:command $directory %1$s "%1$s" $file %2$s "%2$s"',
                dirname($file->getPath()),
                $file->getPath(),
            ),
            $instance->getCommand($context, $command, $params),
        );
    }
}
