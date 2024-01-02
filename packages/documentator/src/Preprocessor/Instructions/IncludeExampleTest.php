<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions;

use Illuminate\Container\Container;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
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
        $instance = Container::getInstance()->make(IncludeExample::class);

        Process::preventStrayProcesses();
        Process::fake();

        self::assertEquals(
            <<<EXPECTED
            ```md
            {$expected}
            ```
            EXPECTED,
            $instance->process($path, $file),
        );

        Process::assertNothingRan();
    }

    public function testProcess(): void {
        $path     = self::getTestData()->path('~runnable.md');
        $file     = basename(self::getTestData()->path('~runnable.md'));
        $command  = self::getTestData()->path('~runnable.run');
        $expected = trim(self::getTestData()->content('~runnable.md'));
        $output   = 'command output';
        $instance = Container::getInstance()->make(IncludeExample::class);

        Process::preventStrayProcesses();
        Process::fake([
            $command => Process::result($output),
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

        Process::assertRan(static function (PendingProcess $process) use ($path, $command): bool {
            return $process->path === dirname($path)
                && $process->command === $command;
        });
    }

    public function testProcessLongOutput(): void {
        $path     = self::getTestData()->path('~runnable.md');
        $file     = self::getTestData()->path('~runnable.md');
        $command  = self::getTestData()->path('~runnable.run');
        $expected = trim(self::getTestData()->content('~runnable.md'));
        $output   = implode("\n", range(0, IncludeExample::Limit + 1));
        $instance = Container::getInstance()->make(IncludeExample::class);

        Process::preventStrayProcesses();
        Process::fake([
            $command => Process::result($output),
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

        Process::assertRan(static function (PendingProcess $process) use ($path, $command): bool {
            return $process->path === dirname($path)
                && $process->command === $command;
        });
    }

    public function testProcessMarkdown(): void {
        $path     = self::getTestData()->path('~runnable.md');
        $file     = basename(self::getTestData()->path('~runnable.md'));
        $command  = self::getTestData()->path('~runnable.run');
        $expected = trim(self::getTestData()->content('~runnable.md'));
        $output   = 'command output';
        $instance = Container::getInstance()->make(IncludeExample::class);

        Process::preventStrayProcesses();
        Process::fake([
            $command => Process::result("<markdown>{$output}</markdown>"),
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

        Process::assertRan(static function (PendingProcess $process) use ($path, $command): bool {
            return $process->path === dirname($path)
                && $process->command === $command;
        });
    }

    public function testProcessMarkdownLongOutput(): void {
        $path     = self::getTestData()->path('~runnable.md');
        $file     = self::getTestData()->path('~runnable.md');
        $command  = self::getTestData()->path('~runnable.run');
        $expected = trim(self::getTestData()->content('~runnable.md'));
        $output   = implode("\n", range(0, IncludeExample::Limit + 1));
        $instance = Container::getInstance()->make(IncludeExample::class);

        Process::preventStrayProcesses();
        Process::fake([
            $command => Process::result("<markdown>{$output}</markdown>"),
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

        Process::assertRan(static function (PendingProcess $process) use ($path, $command): bool {
            return $process->path === dirname($path)
                && $process->command === $command;
        });
    }
}
