<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeTemplate;

use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TemplateDataMissed;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TemplateVariablesMissed;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TemplateVariablesUnused;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\ProcessorHelper;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function dirname;

/**
 * @internal
 */
#[CoversClass(Instruction::class)]
final class InstructionTest extends TestCase {
    public function testInvoke(): void {
        $root     = new Directory(Path::normalize(__DIR__), false);
        $file     = new File(Path::normalize(self::getTestData()->path('.md')), false);
        $params   = new Parameters([
            'a' => 'Relative',
            'b' => 'Inner reference ${a}',
        ]);
        $context  = new Context($root, $file, '/path/to/file.md', '');
        $instance = $this->app()->make(Instruction::class);

        self::assertEquals(
            <<<'FILE'
            # Template Relative

            Content of the file Relative with variable "Inner reference Relative"

            FILE
            ,
            ProcessorHelper::runInstruction($instance, $context, $file, $params),
        );
    }

    public function testInvokeNoData(): void {
        $root     = new Directory(Path::normalize(__DIR__), false);
        $file     = new File(Path::normalize(__FILE__), false);
        $params   = new Parameters([]);
        $context  = new Context($root, $file, $file->getPath(), '');
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new TemplateDataMissed($context),
        );

        ProcessorHelper::runInstruction($instance, $context, $file, $params);
    }

    public function testInvokeVariablesUnused(): void {
        $path     = self::getTestData()->path('.md');
        $root     = new Directory(dirname($path), false);
        $file     = new File($path, false);
        $params   = new Parameters([
            'a' => 'A',
            'b' => 'B',
            'c' => 'C',
            'd' => 'D',
        ]);
        $context  = new Context($root, $file, $file->getName(), '');
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new TemplateVariablesUnused($context, ['c', 'd']),
        );

        ProcessorHelper::runInstruction($instance, $context, $file, $params);
    }

    public function testInvokeVariablesMissed(): void {
        $path     = self::getTestData()->path('.md');
        $root     = new Directory(dirname($path), false);
        $file     = new File($path, false);
        $params   = new Parameters([
            'a' => 'A',
        ]);
        $context  = new Context($root, $file, $file->getName(), '');
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new TemplateVariablesMissed($context, ['b']),
        );

        ProcessorHelper::runInstruction($instance, $context, $file, $params);
    }
}
