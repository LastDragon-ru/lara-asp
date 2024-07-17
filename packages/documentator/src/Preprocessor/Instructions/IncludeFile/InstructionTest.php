<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeFile;

use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\ProcessorHelper;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Instruction::class)]
final class InstructionTest extends TestCase {
    public function testInvoke(): void {
        $path     = self::getTestData()->path('.md');
        $root     = new Directory(Path::normalize(__DIR__), false);
        $file     = new File($path, false);
        $params   = new Parameters('...');
        $target   = $path;
        $context  = new Context($root, $file, $target, '{...}');
        $instance = $this->app()->make(Instruction::class);
        $expected = self::getTestData()->content('.md');

        self::assertEquals($expected, ProcessorHelper::runInstruction($instance, $context, $target, $params));
    }
}
