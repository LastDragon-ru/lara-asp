<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeExample;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithPreprocess;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeExample\Contracts\Runner;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function file_get_contents;
use function implode;
use function mb_trim;
use function range;

/**
 * @internal
 */
#[CoversClass(Instruction::class)]
final class InstructionTest extends TestCase {
    use WithPreprocess;

    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderInvoke')]
    public function testInvoke(string $expected, string $output): void {
        $path    = (new FilePath(__FILE__))->getNormalizedPath();
        $fs      = $this->getFileSystem($path->getDirectoryPath());
        $file    = $fs->getFile($path);
        $params  = new Parameters(self::getTestData()->path('Example.md'));
        $target  = $params->target;
        $context = $this->getPreprocessInstructionContext($fs, $file);

        $this->override(Runner::class, static function (MockInterface $mock) use ($target, $output): void {
            $mock
                ->shouldReceive('__invoke')
                ->withArgs(static function (File $arg) use ($target): bool {
                    return (string) $arg->getPath() === $target;
                })
                ->once()
                ->andReturn($output);
        });

        $instance = $this->app()->make(Instruction::class);
        $actual   = ($instance)($context, $params);

        self::assertSame($expected, $actual);
    }

    public function testInvokeNoRun(): void {
        self::assertFalse($this->app()->bound(Runner::class));

        $path     = (new FilePath(self::getTestData()->path('Example.md')))->getNormalizedPath();
        $fs       = $this->getFileSystem($path->getDirectoryPath());
        $file     = $fs->getFile($path);
        $params   = new Parameters($file->getName());
        $context  = $this->getPreprocessInstructionContext($fs, $file);
        $expected = mb_trim((string) file_get_contents((string) $path));
        $instance = $this->app()->make(Instruction::class);
        $actual   = ($instance)($context, $params);

        self::assertSame(
            <<<EXPECTED
            ```md
            {$expected}
            ```
            EXPECTED,
            $actual,
        );
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{string, string}>
     */
    public static function dataProviderInvoke(): array {
        $long    = implode("\n", range(0, Instruction::Limit + 1));
        $content = <<<'FILE'
            # File

            content of the file
            FILE;

        return [
            'empty output'         => [
                <<<EXPECTED
                ```md
                {$content}
                ```
                EXPECTED,
                '',
            ],
            'short output'         => [
                <<<EXPECTED
                ```md
                {$content}
                ```

                Example output:

                ```plain
                example
                ```
                EXPECTED,
                'example',
            ],
            'long output'          => [
                <<<EXPECTED
                ```md
                {$content}
                ```

                <details><summary>Example output</summary>

                ```plain
                {$long}
                ```

                </details>
                EXPECTED,
                $long,
            ],
            'markdown output'      => [
                <<<EXPECTED
                ```md
                {$content}
                ```

                example
                EXPECTED,
                '<markdown>example</markdown>',
            ],
            'markdown long output' => [
                <<<EXPECTED
                ```md
                {$content}
                ```

                <details><summary>Example output</summary>

                {$long}

                </details>
                EXPECTED,
                "<markdown>{$long}</markdown>",
            ],
            'markdown with links'  => [
                <<<EXPECTED
                ```md
                {$content}
                ```

                [example](InstructionTest/path/to/file.txt)[^fe8f9df8acedaee3-1]

                [^fe8f9df8acedaee3-1]: Footnote.
                EXPECTED,
                <<<'TEXT'
                <markdown>
                [example](./path/to/file.txt)[^1]

                [^1]: Footnote.
                </markdown>
                TEXT,
            ],
        ];
    }
    // </editor-fold>
}
