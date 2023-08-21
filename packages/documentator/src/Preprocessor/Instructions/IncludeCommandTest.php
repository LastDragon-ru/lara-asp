<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions;

use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Utils\Process;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(IncludeFile::class)]
class IncludeCommandTest extends TestCase {
    public function testProcess(): void {
        $path     = 'current/working/directory';
        $expected = 'result';
        $command  = 'command to execute';
        $process  = Mockery::mock(Process::class);
        $process
            ->shouldReceive('run')
            ->with([$command], $path)
            ->once()
            ->andReturn($expected);

        $instance = $this->app->make(IncludeCommand::class, [
            'process' => $process,
        ]);

        self::assertEquals($expected, $instance->process($path, $command));
    }
}
