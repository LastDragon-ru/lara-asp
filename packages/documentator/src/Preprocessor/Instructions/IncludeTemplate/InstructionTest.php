<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeTemplate;

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
    public function testProcessRelative(): void {
        $file     = self::getTestData()->file('.md');
        $params   = new Parameters([
            'a' => 'Relative',
            'b' => 'Inner reference ${a}',
        ]);
        $instance = $this->app()->make(Instruction::class);

        self::assertEquals(
            <<<'FILE'
            # Template Relative

            Content of the file Relative with variable "Inner reference Relative"

            FILE
            ,
            $instance->process($file->getPathname(), $file->getFilename(), $params),
        );
    }

    public function testProcessAbsolute(): void {
        $path     = 'invalid/directory';
        $file     = self::getTestData()->path('.md');
        $params   = new Parameters([
            'a' => 'Absolute',
            'b' => 'Inner reference ${a}',
        ]);
        $instance = $this->app()->make(Instruction::class);

        self::assertEquals(
            <<<'FILE'
            # Template Absolute

            Content of the file Absolute with variable "Inner reference Absolute"

            FILE
            ,
            $instance->process($path, $file, $params),
        );
    }

    public function testProcessNoData(): void {
        $file     = self::getTestData()->file('.md');
        $params   = new Parameters([]);
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new TemplateDataMissed(
                $file->getPathname(),
                $file->getFilename(),
            ),
        );

        $instance->process($file->getPathname(), $file->getFilename(), $params);
    }

    public function testProcessVariablesUnused(): void {
        $file     = self::getTestData()->file('.md');
        $params   = new Parameters([
            'a' => 'A',
            'b' => 'B',
            'c' => 'C',
            'd' => 'D',
        ]);
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new TemplateVariablesUnused(
                $file->getPathname(),
                $file->getFilename(),
                ['c', 'd'],
            ),
        );

        $instance->process($file->getPathname(), $file->getFilename(), $params);
    }

    public function testProcessVariablesMissed(): void {
        $file     = self::getTestData()->file('.md');
        $params   = new Parameters([
            'a' => 'A',
        ]);
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new TemplateVariablesMissed(
                $file->getPathname(),
                $file->getFilename(),
                ['b'],
            ),
        );

        $instance->process($file->getPathname(), $file->getFilename(), $params);
    }
}
