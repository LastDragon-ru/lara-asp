<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeExec;

use Illuminate\Process\Factory;
use Illuminate\Process\PendingProcess;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Node;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\WithProcessor;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

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
        $context  = new Context($file, Mockery::mock(Document::class), new Node());
        $factory  = $this->override(Factory::class, function () use ($command, $expected): Factory {
            $factory = $this->app()->make(Factory::class);
            $factory->preventStrayProcesses();
            $factory->fake([
                $command => $factory->result($expected),
            ]);

            return $factory;
        });
        $instance = $this->app()->make(Instruction::class);

        self::assertSame($expected, $this->getProcessorResult($fs, ($instance)($context, $params)));

        $factory->assertRan(static function (PendingProcess $process) use ($fs, $command): bool {
            return $process->path === (string) $fs->input
                && $process->command === $command;
        });
    }
}
