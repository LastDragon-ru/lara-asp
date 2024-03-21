<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeExample;

use Illuminate\Container\Container;
use Illuminate\Process\Factory;
use Illuminate\Process\PendingProcess;
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
#[CoversClass(Instruction::class)]
final class InstructionTest extends TestCase {
    public function testProcessNoRun(): void {
        $path     = self::getTestData()->path('~example.md');
        $file     = basename(self::getTestData()->path('~example.md'));
        $expected = trim(self::getTestData()->content('~example.md'));
        $factory  = $this->override(Factory::class, static function (): Factory {
            return Container::getInstance()->make(Factory::class)
                ->preventStrayProcesses()
                ->fake();
        });
        $instance = Container::getInstance()->make(Instruction::class);

        self::assertEquals(
            <<<EXPECTED
            ```md
            {$expected}
            ```
            EXPECTED,
            $instance->process($path, $file),
        );

        $factory->assertNothingRan();
    }

    public function testProcess(): void {
        $path     = self::getTestData()->path('~runnable.md');
        $file     = basename(self::getTestData()->path('~runnable.md'));
        $command  = self::getTestData()->path('~runnable.run');
        $expected = trim(self::getTestData()->content('~runnable.md'));
        $output   = 'command output';
        $factory  = $this->override(Factory::class, static function () use ($command, $output): Factory {
            $factory = Container::getInstance()->make(Factory::class);
            $factory->preventStrayProcesses();
            $factory->fake([
                $command => $factory->result($output),
            ]);

            return $factory;
        });
        $instance = Container::getInstance()->make(Instruction::class);

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

        $factory->assertRan(static function (PendingProcess $process) use ($path, $command): bool {
            return $process->path === dirname($path)
                && $process->command === $command;
        });
    }

    public function testProcessLongOutput(): void {
        $path     = self::getTestData()->path('~runnable.md');
        $file     = self::getTestData()->path('~runnable.md');
        $command  = self::getTestData()->path('~runnable.run');
        $expected = trim(self::getTestData()->content('~runnable.md'));
        $output   = implode("\n", range(0, Instruction::Limit + 1));
        $factory  = $this->override(Factory::class, static function () use ($command, $output): Factory {
            $factory = Container::getInstance()->make(Factory::class);
            $factory->preventStrayProcesses();
            $factory->fake([
                $command => $factory->result($output),
            ]);

            return $factory;
        });
        $instance = Container::getInstance()->make(Instruction::class);

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

        $factory->assertRan(static function (PendingProcess $process) use ($path, $command): bool {
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
        $factory  = $this->override(Factory::class, static function () use ($command, $output): Factory {
            $factory = Container::getInstance()->make(Factory::class);
            $factory->preventStrayProcesses();
            $factory->fake([
                $command => $factory->result("<markdown>{$output}</markdown>"),
            ]);

            return $factory;
        });
        $instance = Container::getInstance()->make(Instruction::class);

        self::assertEquals(
            <<<EXPECTED
            ```md
            {$expected}
            ```

            {$output}
            EXPECTED,
            $instance->process($path, $file),
        );

        $factory->assertRan(static function (PendingProcess $process) use ($path, $command): bool {
            return $process->path === dirname($path)
                && $process->command === $command;
        });
    }

    public function testProcessMarkdownLongOutput(): void {
        $path     = self::getTestData()->path('~runnable.md');
        $file     = self::getTestData()->path('~runnable.md');
        $command  = self::getTestData()->path('~runnable.run');
        $expected = trim(self::getTestData()->content('~runnable.md'));
        $output   = implode("\n", range(0, Instruction::Limit + 1));
        $factory  = $this->override(Factory::class, static function () use ($command, $output): Factory {
            $factory = Container::getInstance()->make(Factory::class);
            $factory->preventStrayProcesses();
            $factory->fake([
                $command => $factory->result("<markdown>{$output}</markdown>"),
            ]);

            return $factory;
        });
        $instance = Container::getInstance()->make(Instruction::class);

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

        $factory->assertRan(static function (PendingProcess $process) use ($path, $command): bool {
            return $process->path === dirname($path)
                && $process->command === $command;
        });
    }
}
