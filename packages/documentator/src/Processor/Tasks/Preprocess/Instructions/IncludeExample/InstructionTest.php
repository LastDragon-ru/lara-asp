<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeExample;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Node;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Nop;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeExample\Contracts\Runner;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\ProcessorHelper;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use function implode;
use function range;
use function trim;

/**
 * @internal
 */
#[CoversClass(Instruction::class)]
final class InstructionTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderInvoke')]
    public function testInvoke(string $expected, string $output): void {
        $path    = (new FilePath(__FILE__))->getNormalizedPath();
        $root    = new Directory($path->getDirectoryPath(), false);
        $file    = new File($path, false);
        $params  = new Parameters('...');
        $target  = self::getTestData()->path('Example.md');
        $context = new Context($root, $file, Mockery::mock(Document::class), new Node(), new Nop());

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
        $actual   = ProcessorHelper::runInstruction($instance, $context, $target, $params);

        self::assertEquals($expected, $actual);
    }

    public function testInvokeNoRun(): void {
        self::assertFalse($this->app()->bound(Runner::class));

        $path     = (new FilePath(self::getTestData()->path('Example.md')))->getNormalizedPath();
        $root     = new Directory($path->getDirectoryPath(), false);
        $file     = new File($path, false);
        $params   = new Parameters('...');
        $target   = $file->getName();
        $context  = new Context($root, $file, Mockery::mock(Document::class), new Node(), new Nop());
        $expected = trim($file->getContent());
        $instance = $this->app()->make(Instruction::class);
        $actual   = ProcessorHelper::runInstruction($instance, $context, $target, $params);

        self::assertEquals(
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
