<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions;

use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Utils\Process;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

use function basename;
use function dirname;
use function implode;
use function range;
use function trim;

/**
 * @internal
 */
#[CoversClass(IncludeFile::class)]
class IncludeExampleTest extends TestCase {
    public function testProcessNoRun(): void {
        $path     = self::getTestData()->path('~example.md');
        $file     = basename(self::getTestData()->path('~example.md'));
        $expected = trim(self::getTestData()->content('~example.md'));
        $process  = Mockery::mock(Process::class);
        $process
            ->shouldReceive('run')
            ->never();

        $instance = $this->app->make(IncludeExample::class, [
            'process' => $process,
        ]);

        self::assertEquals(
            <<<EXPECTED
            ```md
            {$expected}
            ```
            EXPECTED,
            $instance->process($path, $file),
        );
    }

    public function testProcess(): void {
        $path     = self::getTestData()->path('~runnable.md');
        $file     = basename(self::getTestData()->path('~runnable.md'));
        $expected = trim(self::getTestData()->content('~runnable.md'));
        $output   = 'command output';
        $process  = Mockery::mock(Process::class);
        $process
            ->shouldReceive('run')
            ->with([self::getTestData()->path('~runnable.run')], dirname($path))
            ->once()
            ->andReturn($output);

        $instance = $this->app->make(IncludeExample::class, [
            'process' => $process,
        ]);

        self::assertEquals(
            <<<EXPECTED
            ```md
            {$expected}
            ```

            Example output:

            ```plain
            {$output}
            ```
            EXPECTED,
            $instance->process($path, $file),
        );
    }

    public function testProcessLongOutput(): void {
        $path     = self::getTestData()->path('~runnable.md');
        $file     = self::getTestData()->path('~runnable.md');
        $expected = trim(self::getTestData()->content('~runnable.md'));
        $output   = implode("\n", range(0, IncludeExample::Limit + 1));
        $process  = Mockery::mock(Process::class);
        $process
            ->shouldReceive('run')
            ->with([self::getTestData()->path('~runnable.run')], dirname($path))
            ->once()
            ->andReturn($output);

        $instance = $this->app->make(IncludeExample::class, [
            'process' => $process,
        ]);

        self::assertEquals(
            <<<EXPECTED
            ```md
            {$expected}
            ```

            <details><summary>Example output</summary>

            ```plain
            {$output}
            ```

            </details>
            EXPECTED,
            $instance->process($path, $file),
        );
    }

    public function testProcessMarkdown(): void {
        $path     = self::getTestData()->path('~runnable.md');
        $file     = basename(self::getTestData()->path('~runnable.md'));
        $expected = trim(self::getTestData()->content('~runnable.md'));
        $output   = 'command output';
        $process  = Mockery::mock(Process::class);
        $process
            ->shouldReceive('run')
            ->with([self::getTestData()->path('~runnable.run')], dirname($path))
            ->once()
            ->andReturn("<markdown>{$output}</markdown>");

        $instance = $this->app->make(IncludeExample::class, [
            'process' => $process,
        ]);

        self::assertEquals(
            <<<EXPECTED
            ```md
            {$expected}
            ```

            {$output}
            EXPECTED,
            $instance->process($path, $file),
        );
    }

    public function testProcessMarkdownLongOutput(): void {
        $path     = self::getTestData()->path('~runnable.md');
        $file     = self::getTestData()->path('~runnable.md');
        $expected = trim(self::getTestData()->content('~runnable.md'));
        $output   = implode("\n", range(0, IncludeExample::Limit + 1));
        $process  = Mockery::mock(Process::class);
        $process
            ->shouldReceive('run')
            ->with([self::getTestData()->path('~runnable.run')], dirname($path))
            ->once()
            ->andReturn("<markdown>{$output}</markdown>");

        $instance = $this->app->make(IncludeExample::class, [
            'process' => $process,
        ]);

        self::assertEquals(
            <<<EXPECTED
            ```md
            {$expected}
            ```

            <details><summary>Example output</summary>

            {$output}

            </details>
            EXPECTED,
            $instance->process($path, $file),
        );
    }
}
