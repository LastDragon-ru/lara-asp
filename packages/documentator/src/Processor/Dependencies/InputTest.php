<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithProcessor;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Input::class)]
final class InputTest extends TestCase {
    use WithProcessor;

    public function testGetPath(): void {
        $fs         = $this->getFileSystem(__DIR__);
        $dependency = new Input();

        self::assertSame($fs->input, $dependency->getPath($fs));
    }

    public function testInvoke(): void {
        $fs         = $this->getFileSystem(new DirectoryPath(__DIR__));
        $file       = $fs->getDirectory(__DIR__);
        $dependency = new Input();

        self::assertEquals($file, $dependency($fs));
    }
}
