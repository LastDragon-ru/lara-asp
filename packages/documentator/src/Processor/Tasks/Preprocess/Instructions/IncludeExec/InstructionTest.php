<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeExec;

use Illuminate\Process\Factory;
use Illuminate\Process\PendingProcess;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithPreprocess;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Instruction::class)]
final class InstructionTest extends TestCase {
    use WithPreprocess;

    public function testInvoke(): void {
        $fs       = $this->getFileSystem(__DIR__);
        $file     = $fs->getFile(new FilePath(__FILE__));
        $params   = new Parameters('command to execute');
        $expected = 'result';
        $command  = $params->target;
        $context  = $this->getPreprocessInstructionContext($fs, $file);
        $factory  = $this->override(Factory::class, function () use ($command, $expected): Factory {
            $factory = $this->app()->make(Factory::class);
            $factory->preventStrayProcesses();
            $factory->fake([
                $command => $factory->result($expected),
            ]);

            return $factory;
        });
        $instance = $this->app()->make(Instruction::class);

        self::assertSame($expected, ($instance)($context, $params));

        $factory->assertRan(static function (PendingProcess $process) use ($fs, $command): bool {
            return $process->path === (string) $fs->input
                && $process->command === $command;
        });
    }
}
