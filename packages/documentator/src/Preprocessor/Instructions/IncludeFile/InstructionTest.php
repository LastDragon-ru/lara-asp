<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeFile;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\ProcessorHelper;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Instruction::class)]
final class InstructionTest extends TestCase {
    public function testInvoke(): void {
        $path     = self::getTestData()->path('.md');
        $file     = new File($path, false);
        $params   = null;
        $context  = Mockery::mock(Context::class);
        $instance = $this->app()->make(Instruction::class);
        $expected = self::getTestData()->content('.md');

        self::assertEquals($expected, ProcessorHelper::runInstruction($instance, $context, $file, $params));
    }
}
