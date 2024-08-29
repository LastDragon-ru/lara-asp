<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeTemplate;

use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Nop;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeTemplate\Exceptions\TemplateDataMissed;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeTemplate\Exceptions\TemplateVariablesMissed;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeTemplate\Exceptions\TemplateVariablesUnused;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\ProcessorHelper;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function dirname;

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
        $root     = new Directory(Path::normalize(__DIR__), false);
        $file     = new File(Path::normalize(__FILE__), false);
        $params   = new Parameters('...', $data);
        $target   = self::getTestData()->path($source);
        $context  = new Context($root, $file, $target, '{...}', new Nop());
        $instance = $this->app()->make(Instruction::class);
        $expected = self::getTestData()->content($expected);

        self::assertEquals($expected, ProcessorHelper::runInstruction($instance, $context, $target, $params));
    }

    public function testInvokeNoData(): void {
        $root     = new Directory(Path::normalize(__DIR__), false);
        $file     = new File(Path::normalize(__FILE__), false);
        $params   = new Parameters('...', []);
        $target   = $file->getPath();
        $context  = new Context($root, $file, $target, '{...}', new Nop());
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new TemplateDataMissed($context),
        );

        ProcessorHelper::runInstruction($instance, $context, $target, $params);
    }

    public function testInvokeVariablesUnused(): void {
        $path     = self::getTestData()->path('.md');
        $root     = new Directory(dirname($path), false);
        $file     = new File($path, false);
        $params   = new Parameters('...', [
            'a' => 'A',
            'b' => 'B',
            'c' => 'C',
            'd' => 'D',
        ]);
        $target   = $file->getPath();
        $context  = new Context($root, $file, $target, '{...}', new Nop());
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new TemplateVariablesUnused($context, ['c', 'd']),
        );

        ProcessorHelper::runInstruction($instance, $context, $target, $params);
    }

    public function testInvokeVariablesMissed(): void {
        $path     = self::getTestData()->path('.md');
        $root     = new Directory(dirname($path), false);
        $file     = new File($path, false);
        $params   = new Parameters('...', [
            'a' => 'A',
        ]);
        $target   = $file->getPath();
        $context  = new Context($root, $file, $target, '{...}', new Nop());
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
