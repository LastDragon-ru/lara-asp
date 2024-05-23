<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeExec;

use Illuminate\Process\Factory;
use Illuminate\Process\PendingProcess;
use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

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
        $context  = new Context($root, $root, $file, $command, $params);
        $factory  = $this->override(Factory::class, function () use ($command, $expected): Factory {
            $factory = $this->app()->make(Factory::class);
            $factory->preventStrayProcesses();
            $factory->fake([
                $command => $factory->result($expected),
            ]);

            return $factory;
        });
        $instance = $this->app()->make(Instruction::class);

        self::assertEquals($expected, ($instance)($context, $command, $params));

        $factory->assertRan(static function (PendingProcess $process) use ($root, $command): bool {
            return $process->path === $root->getPath()
                && $process->command === $command;
        });
    }
}
