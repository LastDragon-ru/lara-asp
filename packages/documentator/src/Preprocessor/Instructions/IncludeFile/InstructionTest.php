<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeFile;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
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
        $path     = self::getTestData()->path('.md');
        $root     = new Directory(dirname($path), false);
        $file     = new File($path, false);
        $params   = null;
        $context  = new Context($root, $root, $file, $file->getPath(), $params);
        $instance = $this->app()->make(Instruction::class);
        $expected = self::getTestData()->content('.md');

        self::assertEquals($expected, $instance->process($context, $expected, $params));
    }
}
