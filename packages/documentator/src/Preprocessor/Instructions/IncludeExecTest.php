<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions;

use Illuminate\Container\Container;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function dirname;

/**
 * @internal
 */
#[CoversClass(IncludeFile::class)]
final class IncludeExecTest extends TestCase {
    public function testProcess(): void {
        $path     = 'current/working/directory/file.md';
        $expected = 'result';
        $command  = 'command to execute';
        $instance = Container::getInstance()->make(IncludeExec::class);

        Process::preventStrayProcesses();
        Process::fake([
            $command => Process::result($expected),
        ]);

        self::assertEquals($expected, $instance->process($path, $command));

        Process::assertRan(static function (PendingProcess $process) use ($path, $command): bool {
            return $process->path === dirname($path)
                && $process->command === $command;
        });
    }
}
