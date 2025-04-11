<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeTemplate;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeTemplate\Exceptions\TemplateDataMissed;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeTemplate\Exceptions\TemplateVariablesMissed;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeTemplate\Exceptions\TemplateVariablesUnused;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\WithPreprocess;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function pathinfo;

use const PATHINFO_EXTENSION;

/**
 * @internal
 */
#[CoversClass(Instruction::class)]
final class InstructionTest extends TestCase {
    use WithPreprocess;

    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param array<string, scalar|null> $data
     */
    #[DataProvider('dataProviderInvoke')]
    public function testInvoke(string $expected, string $source, array $data): void {
        $fs       = $this->getFileSystem(__DIR__);
        $file     = $fs->getFile(__FILE__);
        $params   = new Parameters(self::getTestData()->path($source), $data);
        $context  = $this->getPreprocessInstructionContext($fs, $file);
        $instance = $this->app()->make(Instruction::class);
        $expected = self::getTestData()->content($expected);
        $actual   = $this->getProcessorResult($fs, ($instance)($context, $params));

        if (pathinfo($source, PATHINFO_EXTENSION) === 'md') {
            self::assertInstanceOf(Document::class, $actual);
        } else {
            self::assertIsString($actual);
        }

        self::assertSame($expected, (string) $actual);
    }

    public function testInvokeNoData(): void {
        $fs       = $this->getFileSystem(__DIR__);
        $file     = $fs->getFile(__FILE__);
        $params   = new Parameters((string) $file, []);
        $context  = $this->getPreprocessInstructionContext($fs, $file);
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new TemplateDataMissed($context, $params),
        );

        $this->getProcessorResult($fs, ($instance)($context, $params));
    }

    public function testInvokeVariablesUnused(): void {
        $path     = (new FilePath(self::getTestData()->path('.md')))->getNormalizedPath();
        $fs       = $this->getFileSystem($path->getDirectoryPath());
        $file     = $fs->getFile($path);
        $params   = new Parameters((string) $file, [
            'a' => 'A',
            'b' => 'B',
            'c' => 'C',
            'd' => 'D',
        ]);
        $context  = $this->getPreprocessInstructionContext($fs, $file);
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new TemplateVariablesUnused($context, $params, ['c', 'd']),
        );

        $this->getProcessorResult($fs, ($instance)($context, $params));
    }

    public function testInvokeVariablesMissed(): void {
        $path     = (new FilePath(self::getTestData()->path('.md')))->getNormalizedPath();
        $fs       = $this->getFileSystem($path->getDirectoryPath());
        $file     = $fs->getFile($path);
        $params   = new Parameters((string) $file, [
            'a' => 'A',
        ]);
        $context  = $this->getPreprocessInstructionContext($fs, $file);
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new TemplateVariablesMissed($context, $params, ['b']),
        );

        $this->getProcessorResult($fs, ($instance)($context, $params));
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{string, string, array<string, scalar|null>}>
     */
    public static function dataProviderInvoke(): array {
        return [
            'txt' => ['Text~expected.txt', 'Text.txt', ['a' => 'File', 'b' => 'Variable']],
            'md'  => ['Markdown~expected.md', 'Markdown.md', ['a' => 'File', 'b' => 'Variable']],
        ];
    }
    // </editor-fold>
}
