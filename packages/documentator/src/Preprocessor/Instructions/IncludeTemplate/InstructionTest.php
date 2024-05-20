<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeTemplate;

use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TemplateDataMissed;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TemplateVariablesMissed;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TemplateVariablesUnused;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function dirname;

/**
 * @internal
 */
#[CoversClass(Instruction::class)]
final class InstructionTest extends TestCase {
    public function testProcess(): void {
        $root     = new Directory(Path::normalize(__DIR__), false);
        $file     = new File(Path::normalize(__FILE__), false);
        $params   = new Parameters([
            'a' => 'Relative',
            'b' => 'Inner reference ${a}',
        ]);
        $content  = self::getTestData()->content('.md');
        $context  = new Context($root, $root, $file, '/path/to/file.md', '');
        $instance = $this->app()->make(Instruction::class);

        self::assertEquals(
            <<<'FILE'
            # Template Relative

            Content of the file Relative with variable "Inner reference Relative"

            FILE
            ,
            $instance->process($context, $content, $params),
        );
    }

    public function testProcessNoData(): void {
        $root     = new Directory(Path::normalize(__DIR__), false);
        $file     = new File(Path::normalize(__FILE__), false);
        $params   = new Parameters([]);
        $content  = 'content';
        $context  = new Context($root, $root, $file, $file->getPath(), '');
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new TemplateDataMissed($context),
        );

        $instance->process($context, $content, $params);
    }

    public function testProcessVariablesUnused(): void {
        $path     = self::getTestData()->path('.md');
        $root     = new Directory(dirname($path), false);
        $file     = new File($path, false);
        $params   = new Parameters([
            'a' => 'A',
            'b' => 'B',
            'c' => 'C',
            'd' => 'D',
        ]);
        $content  = $file->getContent();
        $context  = new Context($root, $root, $file, $file->getName(), '');
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new TemplateVariablesUnused($context, ['c', 'd']),
        );

        $instance->process($context, $content, $params);
    }

    public function testProcessVariablesMissed(): void {
        $path     = self::getTestData()->path('.md');
        $root     = new Directory(dirname($path), false);
        $file     = new File($path, false);
        $params   = new Parameters([
            'a' => 'A',
        ]);
        $content  = $file->getContent();
        $context  = new Context($root, $root, $file, $file->getName(), '');
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new TemplateVariablesMissed($context, ['b']),
        );

        $instance->process($context, $content, $params);
    }
}
