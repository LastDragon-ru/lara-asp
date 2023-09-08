<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions;

use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Utils\Process;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

use function dirname;

/**
 * @internal
 */
#[CoversClass(IncludeFile::class)]
class IncludeExecTest extends TestCase {
    public function testProcess(): void {
        $path     = 'current/working/directory/file.md';
        $expected = 'result';
        $command  = 'command to execute';
        $process  = Mockery::mock(Process::class);
        $process
            ->shouldReceive('run')
            ->with(['command', 'to execute'], dirname($path))
            ->once()
            ->andReturn($expected);

        $instance = $this->app->make(IncludeExec::class, [
            'process' => $process,
        ]);

        self::assertEquals($expected, $instance->process($path, $command));
    }
}