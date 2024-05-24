<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeExample;

use Illuminate\Process\Factory;
use Illuminate\Process\PendingProcess;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\ProcessorHelper;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function dirname;
use function implode;
use function range;
use function trim;

/**
 * @internal
 */
#[CoversClass(Instruction::class)]
final class InstructionTest extends TestCase {
    public function testInvokeNoRun(): void {
        $path     = self::getTestData()->path('~example.md');
        $root     = new Directory(dirname($path), false);
        $file     = new File($path, false);
        $params   = null;
        $context  = new Context($root, $file, $file->getName(), $params);
        $content  = self::getTestData()->content('~example.md');
        $expected = trim($content);
        $factory  = $this->override(Factory::class, function (): Factory {
            return $this->app()->make(Factory::class)
                ->preventStrayProcesses()
                ->fake();
        });
        $instance = $this->app()->make(Instruction::class);
        $actual   = ProcessorHelper::runInstruction($instance, $context, $file, $params);

        self::assertEquals(
            <<<EXPECTED
            ```md
            {$expected}
            ```
            EXPECTED,
            $actual,
        );

        $factory->assertNothingRan();
    }

    public function testInvoke(): void {
        $path     = self::getTestData()->path('~runnable.md');
        $root     = new Directory(dirname($path), false);
        $file     = new File($path, false);
        $params   = null;
        $context  = new Context($root, $file, $file->getName(), $params);
        $content  = self::getTestData()->content('~runnable.md');
        $command  = self::getTestData()->path('~runnable.run');
        $expected = trim($content);
        $output   = 'command output';
        $factory  = $this->override(Factory::class, function () use ($command, $output): Factory {
            $factory = $this->app()->make(Factory::class);
            $factory->preventStrayProcesses();
            $factory->fake([
                $command => $factory->result($output),
            ]);

            return $factory;
        });
        $instance = $this->app()->make(Instruction::class);
        $actual   = ProcessorHelper::runInstruction($instance, $context, $file, $params);

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
            $actual,
        );

        $factory->assertRan(static function (PendingProcess $process) use ($path, $command): bool {
            return $process->path === dirname($path)
                && $process->command === $command;
        });
    }

    public function testInvokeLongOutput(): void {
        $path     = self::getTestData()->path('~runnable.md');
        $root     = new Directory(dirname($path), false);
        $file     = new File($path, false);
        $params   = null;
        $context  = new Context($root, $file, $file->getPath(), $params);
        $content  = self::getTestData()->content('~runnable.md');
        $command  = self::getTestData()->path('~runnable.run');
        $expected = trim($content);
        $output   = implode("\n", range(0, Instruction::Limit + 1));
        $factory  = $this->override(Factory::class, function () use ($command, $output): Factory {
            $factory = $this->app()->make(Factory::class);
            $factory->preventStrayProcesses();
            $factory->fake([
                $command => $factory->result($output),
            ]);

            return $factory;
        });
        $instance = $this->app()->make(Instruction::class);
        $actual   = ProcessorHelper::runInstruction($instance, $context, $file, $params);

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
            $actual,
        );

        $factory->assertRan(static function (PendingProcess $process) use ($path, $command): bool {
            return $process->path === dirname($path)
                && $process->command === $command;
        });
    }

    public function testInvokeMarkdown(): void {
        $path     = self::getTestData()->path('~runnable.md');
        $root     = new Directory(dirname($path), false);
        $file     = new File($path, false);
        $params   = null;
        $context  = new Context($root, $file, $file->getName(), $params);
        $content  = self::getTestData()->content('~runnable.md');
        $command  = self::getTestData()->path('~runnable.run');
        $expected = trim($content);
        $output   = 'command output';
        $factory  = $this->override(Factory::class, function () use ($command, $output): Factory {
            $factory = $this->app()->make(Factory::class);
            $factory->preventStrayProcesses();
            $factory->fake([
                $command => $factory->result("<markdown>{$output}</markdown>"),
            ]);

            return $factory;
        });
        $instance = $this->app()->make(Instruction::class);
        $actual   = ProcessorHelper::runInstruction($instance, $context, $file, $params);

        self::assertEquals(
            <<<EXPECTED
            ```md
            {$expected}
            ```

            {$output}
            EXPECTED,
            $actual,
        );

        $factory->assertRan(static function (PendingProcess $process) use ($path, $command): bool {
            return $process->path === dirname($path)
                && $process->command === $command;
        });
    }

    public function testInvokeMarkdownLongOutput(): void {
        $path     = self::getTestData()->path('~runnable.md');
        $root     = new Directory(dirname($path), false);
        $file     = new File($path, false);
        $params   = null;
        $context  = new Context($root, $file, $file->getPath(), $params);
        $content  = self::getTestData()->content('~runnable.md');
        $command  = self::getTestData()->path('~runnable.run');
        $expected = trim($content);
        $output   = implode("\n", range(0, Instruction::Limit + 1));
        $factory  = $this->override(Factory::class, function () use ($command, $output): Factory {
            $factory = $this->app()->make(Factory::class);
            $factory->preventStrayProcesses();
            $factory->fake([
                $command => $factory->result("<markdown>{$output}</markdown>"),
            ]);

            return $factory;
        });
        $instance = $this->app()->make(Instruction::class);
        $actual   = ProcessorHelper::runInstruction($instance, $context, $file, $params);

        self::assertEquals(
            <<<EXPECTED
            ```md
            {$expected}
            ```

            <details><summary>Example output</summary>

            {$output}

            </details>
            EXPECTED,
            $actual,
        );

        $factory->assertRan(static function (PendingProcess $process) use ($path, $command): bool {
            return $process->path === dirname($path)
                && $process->command === $command;
        });
    }
}
