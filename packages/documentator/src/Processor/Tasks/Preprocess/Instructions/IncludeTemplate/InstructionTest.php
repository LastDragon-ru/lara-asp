<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeTemplate;

use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Node;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Nop;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeTemplate\Exceptions\TemplateDataMissed;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeTemplate\Exceptions\TemplateVariablesMissed;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeTemplate\Exceptions\TemplateVariablesUnused;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\ProcessorHelper;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use function pathinfo;
use const PATHINFO_EXTENSION;

/**
 * @internal
 */
#[CoversClass(Instruction::class)]
final class InstructionTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param array<string, scalar|null> $data
     */
    #[DataProvider('dataProviderInvoke')]
    public function testInvoke(string $expected, string $source, array $data): void {
        $root     = new Directory((new DirectoryPath(__DIR__))->getNormalizedPath());
        $file     = new File((new FilePath(__FILE__))->getNormalizedPath());
        $params   = new Parameters('...', $data);
        $target   = self::getTestData()->path($source);
        $context  = new Context($root, $file, Mockery::mock(Document::class), new Node(), new Nop());
        $instance = $this->app()->make(Instruction::class);
        $expected = self::getTestData()->content($expected);
        $actual   = ProcessorHelper::runInstruction($instance, $context, $target, $params);

        if (pathinfo($source, PATHINFO_EXTENSION) === 'md') {
            self::assertInstanceOf(Document::class, $actual);
        } else {
            self::assertIsString($actual);
        }

        self::assertEquals($expected, (string) $actual);
    }

    public function testInvokeNoData(): void {
        $root     = new Directory((new DirectoryPath(__DIR__))->getNormalizedPath());
        $file     = new File((new FilePath(__FILE__))->getNormalizedPath());
        $params   = new Parameters('...', []);
        $target   = $file->getPath();
        $context  = new Context($root, $file, Mockery::mock(Document::class), new Node(), new Nop());
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new TemplateDataMissed($context),
        );

        ProcessorHelper::runInstruction($instance, $context, $target, $params);
    }

    public function testInvokeVariablesUnused(): void {
        $path     = (new FilePath(self::getTestData()->path('.md')))->getNormalizedPath();
        $root     = new Directory($path->getDirectoryPath());
        $file     = new File($path);
        $params   = new Parameters('...', [
            'a' => 'A',
            'b' => 'B',
            'c' => 'C',
            'd' => 'D',
        ]);
        $target   = $file->getPath();
        $context  = new Context($root, $file, Mockery::mock(Document::class), new Node(), new Nop());
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new TemplateVariablesUnused($context, ['c', 'd']),
        );

        ProcessorHelper::runInstruction($instance, $context, $target, $params);
    }

    public function testInvokeVariablesMissed(): void {
        $path     = (new FilePath(self::getTestData()->path('.md')))->getNormalizedPath();
        $root     = new Directory($path->getDirectoryPath());
        $file     = new File($path);
        $params   = new Parameters('...', [
            'a' => 'A',
        ]);
        $target   = $file->getPath();
        $context  = new Context($root, $file, Mockery::mock(Document::class), new Node(), new Nop());
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new TemplateVariablesMissed($context, ['b']),
        );

        ProcessorHelper::runInstruction($instance, $context, $target, $params);
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
