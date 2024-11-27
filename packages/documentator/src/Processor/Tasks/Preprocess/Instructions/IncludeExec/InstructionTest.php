<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeExec;

use Illuminate\Process\Factory;
use Illuminate\Process\PendingProcess;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Nop;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Reference\Block;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\ProcessorHelper;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

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
        $factory  = $this->override(Factory::class, function () use ($command, $expected): Factory {
            $factory = $this->app()->make(Factory::class);
            $factory->preventStrayProcesses();
            $factory->fake([
                $command => $factory->result($expected),
            ]);

            return $factory;
        });
        $instance = $this->app()->make(Instruction::class);

        self::assertEquals($expected, ProcessorHelper::runInstruction($instance, $context, $command, $params));

        $factory->assertRan(static function (PendingProcess $process) use ($root, $command): bool {
            return $process->path === (string) $root->getPath()
                && $process->command === $command;
        });
    }
}
