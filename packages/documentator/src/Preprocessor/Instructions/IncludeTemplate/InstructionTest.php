<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeTemplate;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TemplateDataMissed;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TemplateVariablesMissed;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TemplateVariablesUnused;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Instruction::class)]
final class InstructionTest extends TestCase {
    public function testProcess(): void {
        $params   = new Parameters([
            'a' => 'Relative',
            'b' => 'Inner reference ${a}',
        ]);
        $content  = self::getTestData()->content('.md');
        $context  = new Context('/path/to/file.md', '/path/to/file.md', '');
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
        $file     = 'path/to/file.md';
        $params   = new Parameters([]);
        $content  = 'content';
        $context  = new Context($file, $file, '');
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new TemplateDataMissed($context),
        );

        $instance->process($context, $content, $params);
    }

    public function testProcessVariablesUnused(): void {
        $file     = self::getTestData()->file('.md');
        $params   = new Parameters([
            'a' => 'A',
            'b' => 'B',
            'c' => 'C',
            'd' => 'D',
        ]);
        $content  = self::getTestData()->content('.md');
        $context  = new Context($file->getPathname(), $file->getFilename(), '');
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new TemplateVariablesUnused($context, ['c', 'd']),
        );

        $instance->process($context, $content, $params);
    }

    public function testProcessVariablesMissed(): void {
        $file     = self::getTestData()->file('.md');
        $params   = new Parameters([
            'a' => 'A',
        ]);
        $content  = self::getTestData()->content('.md');
        $context  = new Context($file->getPathname(), $file->getFilename(), '');
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new TemplateVariablesMissed($context, ['b']),
        );

        $instance->process($context, $content, $params);
    }
}
