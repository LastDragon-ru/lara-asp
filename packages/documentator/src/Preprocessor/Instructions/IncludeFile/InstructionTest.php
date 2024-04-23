<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeFile;

use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Instruction::class)]
final class InstructionTest extends TestCase {
    public function testProcessRelative(): void {
        $file     = self::getTestData()->file('.md');
        $instance = $this->app()->make(Instruction::class);
        $expected = self::getTestData()->content('.md');

        self::assertEquals($expected, $instance->process($file->getPathname(), $file->getFilename()));
    }

    public function testProcessAbsolute(): void {
        $path     = 'invalid/directory';
        $file     = self::getTestData()->path('.md');
        $instance = $this->app()->make(Instruction::class);
        $expected = self::getTestData()->content('.md');

        self::assertEquals($expected, $instance->process($path, $file));
    }
}
