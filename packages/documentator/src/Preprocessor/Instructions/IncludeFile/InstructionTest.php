<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeFile;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Instruction::class)]
final class InstructionTest extends TestCase {
    public function testProcess(): void {
        $file     = self::getTestData()->path('.md');
        $params   = null;
        $context  = new Context($file, $file, $params);
        $instance = $this->app()->make(Instruction::class);
        $expected = self::getTestData()->content('.md');

        self::assertEquals($expected, $instance->process($context, $expected, $params));
    }
}
